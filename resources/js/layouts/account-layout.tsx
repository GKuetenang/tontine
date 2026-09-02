import { router, usePage } from '@inertiajs/react';
import {
    CoinsIcon,
    HandCoinsIcon,
    LandmarkIcon,
    UsersIcon,
} from 'lucide-react';
import type { PropsWithChildren } from 'react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import account from '@/routes/account';

const items = [
    {
        value: 'groups',
        label: 'Mes réunions',
        href: account.index().url,
        icon: UsersIcon,
    },
    {
        value: 'insurance',
        label: 'Mon assurance',
        href: account.insurance.index().url,
        icon: HandCoinsIcon,
    },
    {
        value: 'contributions',
        label: 'Mes cotisations',
        href: account.contributions.index().url,
        icon: CoinsIcon,
    },
    {
        value: 'loans',
        label: 'Mes prêts',
        href: account.loans.index().url,
        icon: LandmarkIcon,
    },
] as const;

export function AccountLayout({ children }: PropsWithChildren) {
    const { url } = usePage();
    const activeItem =
        items.find(
            (item) => item.value !== 'groups' && url.startsWith(item.href),
        ) ?? items[0];

    return (
        <Tabs
            value={activeItem.value}
            onValueChange={(value) => {
                const item = items.find(
                    (candidate) => candidate.value === value,
                );

                if (item) {
                    router.visit(item.href);
                }
            }}
            className="w-full"
        >
            <TabsList className="h-auto w-full justify-start gap-2 overflow-x-auto">
                {items.map((item) => (
                    <TabsTrigger
                        key={item.value}
                        value={item.value}
                        className="hover:bg-sidebar-accent data-[state=active]:bg-sidebar-accent data-[state=active]:shadow-none!"
                    >
                        <item.icon className="size-4" />
                        {item.label}
                    </TabsTrigger>
                ))}
            </TabsList>
            <TabsContent value={activeItem.value} className="mt-6 space-y-6">
                {children}
            </TabsContent>
        </Tabs>
    );
}
