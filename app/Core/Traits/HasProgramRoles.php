<?php

namespace App\Core\Traits;

use App\Core\Models\OrganizationProgram;

trait HasProgramRoles
{
    /**
     * Get the programs that the user belongs to.
     */
    public function programs()
    {
        return $this->belongsToMany(OrganizationProgram::class, 'program_members', 'user_id', 'program_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Check if the user has a specific role in a program.
     *
     * @param OrganizationProgram|string|int $program Program instance, ID, or slug
     * @param string $role
     * @return bool
     */
    public function hasProgramRole($program, string $role): bool
    {
        // If super admin, they have all access (optional logic, can be removed if strict)
        // if ($this->hasRole('super_admin')) return true; 

        if ($program instanceof OrganizationProgram) {
            $programId = $program->id;
        } elseif (is_numeric($program)) {
            $programId = $program;
        } else {
            // Assuming slug
            $p = OrganizationProgram::where('slug', $program)->first();
            if (!$p) return false;
            $programId = $p->id;
        }

        return $this->programs()
                    ->where('organization_programs.id', $programId)
                    ->wherePivot('role', $role)
                    ->exists();
    }
    
    /**
     * Check if user is an admin of the program
     */
    public function isProgramAdmin($program): bool
    {
        return $this->hasProgramRole($program, 'admin');
    }
}
