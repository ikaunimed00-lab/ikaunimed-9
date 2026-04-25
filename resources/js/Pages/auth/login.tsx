import { Head, Link, useForm } from '@inertiajs/react';
import { Chrome, ArrowLeft, Mail, Lock, Eye, EyeOff } from 'lucide-react';
import { FormEventHandler, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLogoIcon from '@/components/app-logo-icon';

export default function Login({ status, canResetPassword, canRegister }: { status?: string, canResetPassword: boolean, canRegister: boolean }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });
    
    const [showPassword, setShowPassword] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <div className="min-h-screen w-full flex bg-slate-50">
            <Head title="Login - IKA UNIMED" />
            
            {/* Left Side - Hero/Branding */}
            <div className="hidden lg:flex w-1/2 bg-[#006837] relative overflow-hidden flex-col justify-between p-12 text-white">
                <div className="absolute inset-0 z-0 opacity-20">
                    <img 
                        src="/images/hero_slide_administrasi.png" 
                        alt="Background" 
                        className="w-full h-full object-cover mix-blend-overlay"
                    />
                    <div className="absolute inset-0 bg-gradient-to-br from-[#006837] to-[#004d29] mix-blend-multiply" />
                </div>
                
                {/* Pattern Overlay */}
                <div className="absolute inset-0 z-0 opacity-10" style={{ backgroundImage: 'radial-gradient(circle at 2px 2px, white 1px, transparent 0)', backgroundSize: '32px 32px' }}></div>

                <div className="relative z-10">
                    <Link href="/" className="flex items-center gap-3 text-white/90 hover:text-white transition-colors group">
                        <div className="p-2 bg-white/10 rounded-lg backdrop-blur-sm group-hover:bg-white/20 transition-all">
                            <ArrowLeft className="w-5 h-5" />
                        </div>
                        <span className="font-medium tracking-wide">Kembali ke Beranda</span>
                    </Link>
                </div>

                <div className="relative z-10 space-y-6 max-w-lg">
                    <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FFD700]/20 text-[#FFD700] border border-[#FFD700]/30 text-sm font-medium">
                        <span className="w-2 h-2 rounded-full bg-[#FFD700] animate-pulse"></span>
                        Portal Alumni IKA UNIMED
                    </div>
                    <h1 className="text-5xl font-bold leading-tight tracking-tight">
                        Sinergi Alumni,<br/>
                        <span className="text-[#FFD700]">Membangun Negeri.</span>
                    </h1>
                    <p className="text-lg text-slate-200 leading-relaxed">
                        Bergabunglah dengan ribuan alumni Universitas Negeri Medan lainnya. Akses layanan karir, donasi, dan jejaring alumni dalam satu platform terintegrasi.
                    </p>
                </div>

                <div className="relative z-10 text-sm text-slate-300/80">
                    &copy; {new Date().getFullYear()} IKA UNIMED. All rights reserved.
                </div>
            </div>

            {/* Right Side - Login Form */}
            <div className="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 relative">
                {/* Mobile Back Button */}
                <div className="absolute top-6 left-6 lg:hidden">
                    <Link href="/" className="p-2 -ml-2 rounded-full hover:bg-slate-100 flex items-center gap-2 text-slate-600 transition-colors">
                        <ArrowLeft className="w-5 h-5" />
                        <span className="text-sm font-medium">Kembali</span>
                    </Link>
                </div>

                <div className="w-full max-w-md space-y-8">
                    <div className="text-center lg:text-left space-y-2">
                        <div className="flex justify-center lg:justify-start mb-6">
                             <div className="h-12 w-12 bg-[#006837] rounded-xl flex items-center justify-center shadow-lg shadow-green-900/10">
                                <AppLogoIcon className="w-8 h-8 text-white fill-current" />
                            </div>
                        </div>
                        <h2 className="text-3xl font-bold tracking-tight text-slate-900">Selamat Datang Kembali</h2>
                        <p className="text-slate-500">
                            Masuk ke akun Anda untuk melanjutkan akses layanan.
                        </p>
                    </div>

                    {status && (
                        <div className="p-4 rounded-lg bg-green-50 text-green-700 text-sm font-medium border border-green-100 flex items-center gap-2">
                            <span className="flex h-2 w-2 rounded-full bg-green-600"></span>
                            {status}
                        </div>
                    )}

                    <div className="space-y-6">
                        {/* Social Login */}
                        <a 
                            href="/auth/google"
                            className="relative flex items-center justify-center w-full px-4 py-3 border border-slate-200 rounded-xl font-medium text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-300 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#006837]/20 shadow-sm hover:shadow group"
                        >
                            <Chrome className="w-5 h-5 text-blue-600 mr-3 group-hover:scale-110 transition-transform" />
                            <span>Masuk dengan Google</span>
                        </a>

                        <div className="relative">
                            <div className="absolute inset-0 flex items-center">
                                <div className="w-full border-t border-slate-200"></div>
                            </div>
                            <div className="relative flex justify-center text-xs uppercase">
                                <span className="bg-slate-50 px-4 text-slate-400 font-medium tracking-wider">Atau masuk dengan email</span>
                            </div>
                        </div>

                        <form onSubmit={submit} className="space-y-5">
                            <div className="space-y-2">
                                <Label htmlFor="email" className="text-slate-700 font-semibold">Email</Label>
                                <div className="relative group">
                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <Mail className="h-5 w-5 text-slate-400 group-focus-within:text-[#006837] transition-colors" />
                                    </div>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        className="pl-10 h-11 border-slate-200 focus:border-[#006837] focus:ring-[#006837]"
                                        placeholder="nama@email.com"
                                        autoComplete="username"
                                        autoFocus
                                        required
                                    />
                                </div>
                                <InputError message={errors.email} />
                            </div>

                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="password" className="text-slate-700 font-semibold">Password</Label>
                                    {canResetPassword && (
                                        <Link
                                            href={route('password.request')}
                                            className="text-sm font-medium text-[#006837] hover:text-[#00522c] transition-colors"
                                        >
                                            Lupa password?
                                        </Link>
                                    )}
                                </div>
                                <div className="relative group">
                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <Lock className="h-5 w-5 text-slate-400 group-focus-within:text-[#006837] transition-colors" />
                                    </div>
                                    <Input
                                        id="password"
                                        type={showPassword ? "text" : "password"}
                                        name="password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        className="pl-10 pr-10 h-11 border-slate-200 focus:border-[#006837] focus:ring-[#006837]"
                                        placeholder="••••••••"
                                        autoComplete="current-password"
                                        required
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPassword(!showPassword)}
                                        className="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
                                    >
                                        {showPassword ? (
                                            <EyeOff className="h-5 w-5" />
                                        ) : (
                                            <Eye className="h-5 w-5" />
                                        )}
                                    </button>
                                </div>
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    checked={data.remember}
                                    onCheckedChange={(checked) => setData('remember', !!checked)}
                                    className="border-slate-300 text-[#006837] focus:ring-[#006837]"
                                />
                                <Label htmlFor="remember" className="ml-2 text-sm text-slate-600 font-medium cursor-pointer">
                                    Ingat saya
                                </Label>
                            </div>

                            <Button
                                type="submit"
                                className="w-full h-11 bg-[#006837] hover:bg-[#00522c] text-white font-semibold text-base shadow-md shadow-green-900/10 transition-all hover:shadow-lg hover:shadow-green-900/20"
                                disabled={processing}
                            >
                                {processing && <Spinner className="mr-2 h-4 w-4" />}
                                Masuk
                            </Button>
                        </form>

                        {canRegister && (
                            <div className="text-center pt-4">
                                <p className="text-sm text-slate-500">
                                    Belum punya akun?{' '}
                                    <Link
                                        href={route('register')}
                                        className="font-semibold text-[#006837] hover:text-[#00522c] transition-colors hover:underline"
                                    >
                                        Daftar Sekarang
                                    </Link>
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
