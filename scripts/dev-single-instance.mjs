import { spawn, spawnSync } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const lockDir = join(process.cwd(), 'storage', 'framework', 'cache', 'dev-locks');
const lockFile = join(lockDir, 'vite-dev.lock.json');
const forceMode = process.argv.includes('--force');

function isProcessRunning(pid) {
    if (!Number.isInteger(pid) || pid <= 0) {
        return false;
    }

    try {
        process.kill(pid, 0);
        return true;
    } catch {
        return false;
    }
}

function readLock() {
    if (!existsSync(lockFile)) {
        return null;
    }

    try {
        const raw = readFileSync(lockFile, 'utf8');
        const parsed = JSON.parse(raw);
        if (typeof parsed?.pid !== 'number') {
            return null;
        }

        return parsed;
    } catch {
        return null;
    }
}

function removeLock() {
    try {
        rmSync(lockFile);
    } catch {
        // noop
    }
}

function isViteProcessForProjectPath(commandLine, projectPath) {
    if (typeof commandLine !== 'string' || commandLine.trim() === '') {
        return false;
    }

    const lowerCommand = commandLine.toLowerCase();
    const lowerPath = projectPath.toLowerCase();

    return lowerCommand.includes('vite') && lowerCommand.includes(lowerPath);
}

function getProcessCommandByPid(pid) {
    if (!Number.isInteger(pid) || pid <= 0) {
        return null;
    }

    if (process.platform === 'win32') {
        const probe = spawnSync(
            'powershell.exe',
            [
                '-NoProfile',
                '-Command',
                `Get-CimInstance Win32_Process -Filter "ProcessId = ${pid}" | Select-Object -ExpandProperty CommandLine`,
            ],
            { encoding: 'utf8' },
        );

        if (probe.status !== 0) {
            return null;
        }

        const commandLine = probe.stdout.trim();
        return commandLine === '' ? null : commandLine;
    }

    const probe = spawnSync('ps', ['-p', String(pid), '-o', 'command='], { encoding: 'utf8' });
    if (probe.status !== 0) {
        return null;
    }

    const commandLine = probe.stdout.trim();
    return commandLine === '' ? null : commandLine;
}

function findExistingViteProcessByProjectPath() {
    const projectPath = process.cwd();

    if (process.platform === 'win32') {
        const escapedPath = projectPath.replace(/'/g, "''");
        const probe = spawnSync(
            'powershell.exe',
            [
                '-NoProfile',
                '-Command',
                `$projectPath = '${escapedPath}'; ` +
                    "Get-CimInstance Win32_Process | " +
                    "Where-Object { $_.CommandLine -and $_.CommandLine -match 'vite' -and $_.CommandLine -like \"*$projectPath*\" } | " +
                    'Select-Object -ExpandProperty ProcessId',
            ],
            { encoding: 'utf8' },
        );

        if (probe.status !== 0) {
            return null;
        }

        const rawPids = probe.stdout
            .split(/\r?\n/g)
            .map((line) => Number.parseInt(line.trim(), 10))
            .filter((pid) => Number.isInteger(pid) && pid > 0 && pid !== process.pid);

        if (rawPids.length === 0) {
            return null;
        }

        return rawPids[0] ?? null;
    }

    const probe = spawnSync('ps', ['-ax', '-o', 'pid=,command='], { encoding: 'utf8' });
    if (probe.status !== 0) {
        return null;
    }

    const projectPathLower = projectPath.toLowerCase();
    const lines = probe.stdout.split('\n');
    for (const line of lines) {
        const trimmed = line.trim();
        if (trimmed === '') {
            continue;
        }

        const splitIndex = trimmed.indexOf(' ');
        if (splitIndex === -1) {
            continue;
        }

        const pidText = trimmed.slice(0, splitIndex).trim();
        const command = trimmed.slice(splitIndex + 1).trim();
        const pid = Number.parseInt(pidText, 10);
        if (!Number.isInteger(pid) || pid <= 0 || pid === process.pid) {
            continue;
        }

        const commandLower = command.toLowerCase();
        if (!commandLower.includes('vite')) {
            continue;
        }

        if (!commandLower.includes(projectPathLower)) {
            continue;
        }

        return pid;
    }

    return null;
}

mkdirSync(lockDir, { recursive: true });

const existingLock = readLock();
if (existingLock && isProcessRunning(existingLock.pid)) {
    const lockPidCommand = getProcessCommandByPid(existingLock.pid);
    const lockPidMatchesProject = isViteProcessForProjectPath(lockPidCommand, process.cwd());

    if (lockPidMatchesProject) {
        const startedAt = existingLock.startedAt ?? 'unknown time';
        console.error(
            `[dev-guard] Vite dev server is already running (PID ${existingLock.pid}, started ${startedAt}).`,
        );
        console.error('[dev-guard] Stop that process first, then run "npm run dev" again.');
        process.exit(1);
    }

    if (forceMode) {
        console.warn(
            `[dev-guard] Stale lock detected at PID ${existingLock.pid}. Cleaning lock because --force is enabled.`,
        );
    } else {
        console.warn(
            `[dev-guard] Stale lock detected at PID ${existingLock.pid}. Cleaning lock automatically (process is not Vite for this project).`,
        );
    }
}

const existingVitePid = findExistingViteProcessByProjectPath();
if (existingVitePid && isProcessRunning(existingVitePid)) {
    console.error(
        `[dev-guard] Another Vite process is active for this project (PID ${existingVitePid}).`,
    );
    console.error('[dev-guard] Stop that process first, then run "npm run dev" again.');
    process.exit(1);
}

removeLock();
writeFileSync(
    lockFile,
    JSON.stringify(
        {
            pid: process.pid,
            cwd: process.cwd(),
            startedAt: new Date().toISOString(),
        },
        null,
        2,
    ),
    'utf8',
);

let cleaned = false;
function cleanupAndExit(code = 0) {
    if (!cleaned) {
        cleaned = true;
        removeLock();
    }

    process.exit(code);
}

process.on('SIGINT', () => cleanupAndExit(130));
process.on('SIGTERM', () => cleanupAndExit(143));
process.on('SIGHUP', () => cleanupAndExit(129));
process.on('exit', () => {
    if (!cleaned) {
        removeLock();
    }
});

const viteArgs = process.argv.slice(2).filter((arg) => arg !== '--force');
const viteCommand = ['npx', 'vite', ...viteArgs].join(' ');
const vite = spawn(viteCommand, {
    stdio: 'inherit',
    shell: true,
});

vite.on('exit', (code, signal) => {
    if (!cleaned) {
        removeLock();
        cleaned = true;
    }

    if (signal) {
        process.kill(process.pid, signal);
        return;
    }

    process.exit(code ?? 0);
});

vite.on('error', (error) => {
    if (!cleaned) {
        removeLock();
        cleaned = true;
    }

    console.error(`[dev-guard] Failed to start Vite: ${error.message}`);
    process.exit(1);
});
