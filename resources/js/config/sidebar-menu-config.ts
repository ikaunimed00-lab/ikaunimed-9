import { LayoutDashboard, GraduationCap } from "lucide-react";

export interface SidebarItem {
    label: string;
    route: string;
    icon: any;
    requiredPermissions?: string[];
}

export interface SidebarGroup {
    group: string;
    items: SidebarItem[];
    requiredPermissions?: string[];
}

export const SIDEBAR_MENU: SidebarGroup[] = [
    {
        group: "DASHBOARD",
        items: [
            {
                label: "Dashboard",
                route: "dashboard",
                icon: LayoutDashboard,
            },
        ],
    },
    {
        group: "LMS - LEARNER",
        requiredPermissions: ["elearning.participant.enroll"],
        items: [
            {
                label: "Kursus Saya",
                route: "dashboard.elearning.learner.courses.index",
                icon: GraduationCap,
                requiredPermissions: ["elearning.participant.enroll"],
            },
        ],
    },
    {
        group: "LMS - INSTRUCTOR",
        requiredPermissions: [
            "elearning.course.view",
            "elearning.course.view_own",
            "elearning.course.create",
        ],
        items: [
            {
                label: "Kursus Saya (Kelola)",
                route: "dashboard.courses.index",
                icon: GraduationCap,
                requiredPermissions: [
                    "elearning.course.view",
                    "elearning.course.view_own",
                ],
            },
        ],
    },
    {
        group: "LMS - MODERATION",
        requiredPermissions: ["elearning.course.view_any"],
        items: [
            {
                label: "Dashboard LMS (Moderator)",
                route: "dashboard.elearning.moderator.courses.index",
                icon: GraduationCap,
                requiredPermissions: ["elearning.course.view_any"],
            },
        ],
    },
    {
        group: "LMS - SYSTEM",
        requiredPermissions: ["elearning.course.archive"],
        items: [],
    },
];
