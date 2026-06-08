import { createInertiaApp } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app/layout';
import AppProfessionalSidebarLayout from '@/layouts/app/professional-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name, page) => {
        const isProfessional = Boolean(
            (page.props as { auth?: { isProfessional?: boolean } })?.auth
                ?.isProfessional,
        );

        switch (true) {
            case name === 'welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [
                    isProfessional ? AppProfessionalSidebarLayout : AppLayout,
                    SettingsLayout,
                ];
            case name.startsWith('dashboard/'):
                return [AppProfessionalSidebarLayout, SettingsLayout];
            default:
                return isProfessional
                    ? AppProfessionalSidebarLayout
                    : AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
