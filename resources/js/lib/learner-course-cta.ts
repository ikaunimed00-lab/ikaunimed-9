export interface LearnerCourseEligibility {
    is_enrolled?: boolean;
    can_enroll?: boolean;
    requires_premium?: boolean;
    is_premium_member?: boolean;
    is_locked?: boolean;
    reason?: string | null;
}

export type LearnerCoursePrimaryAction =
    | 'upgrade_premium'
    | 'continue_learning'
    | 'join_course'
    | 'login'
    | 'view_course';

export interface LearnerCoursePrimaryActionUi {
    label: string;
    className: string;
    showCartIcon?: boolean;
}

interface ResolvePrimaryActionOptions {
    fallback?: LearnerCoursePrimaryAction;
}

export const resolveLearnerCoursePrimaryAction = (
    eligibility?: LearnerCourseEligibility,
    options: ResolvePrimaryActionOptions = {},
): LearnerCoursePrimaryAction => {
    const fallback = options.fallback ?? 'view_course';

    if (eligibility?.is_locked) return 'upgrade_premium';
    if (eligibility?.reason === 'login_required') return 'login';
    if (eligibility?.is_enrolled) return 'continue_learning';
    if (eligibility?.can_enroll) return 'join_course';
    if (eligibility?.reason === 'course_paid') return 'view_course';

    return fallback;
};

interface ResolvePrimaryActionUiOptions {
    size?: 'table' | 'mobile' | 'card';
}

const primaryActionBaseClassBySize: Record<NonNullable<ResolvePrimaryActionUiOptions['size']>, string> = {
    table: 'inline-flex items-center rounded-md px-3 py-1.5 text-xs font-medium',
    mobile: 'inline-flex items-center rounded-md px-3 py-2 text-xs font-medium',
    card: 'w-full inline-flex items-center justify-center rounded-md px-3 py-2 text-xs font-semibold',
};

export const resolveLearnerCoursePrimaryActionUi = (
    action: LearnerCoursePrimaryAction,
    options: ResolvePrimaryActionUiOptions = {},
): LearnerCoursePrimaryActionUi => {
    const size = options.size ?? 'table';
    const baseClass = primaryActionBaseClassBySize[size];

    if (action === 'upgrade_premium') {
        return {
            label: 'Upgrade ke Premium Member',
            className: `${baseClass} gap-1 bg-amber-600 text-white hover:bg-amber-700`,
            showCartIcon: true,
        };
    }

    if (action === 'login') {
        return {
            label: 'Login untuk Gabung',
            className: `${baseClass} border border-emerald-300 text-emerald-700 hover:bg-emerald-50`,
        };
    }

    if (action === 'view_course') {
        return {
            label: 'Lihat Detail Kelas',
            className: `${baseClass} border border-gray-300 text-gray-700 hover:bg-gray-50`,
        };
    }

    return {
        label: action === 'join_course' ? 'Gabung Kelas' : 'Lanjut Belajar',
        className: `${baseClass} bg-emerald-600 text-white hover:bg-emerald-700`,
    };
};
