import type { InertiaLinkProps } from '@inertiajs/react';
import type { ClassValue } from 'clsx';
import { clsx } from 'clsx';
import {
    CalendarDaysIcon,
    ChartNoAxesColumnIcon,
    CircleDollarSignIcon,
    HandHeartIcon,
    LandmarkIcon,
    PiggyBankIcon,
    ReceiptTextIcon,
    LayoutDashboardIcon,
    ShieldCheckIcon,
    SettingsIcon,
    ShuffleIcon,
    UsersIcon,
    UserRoundCogIcon,
} from 'lucide-react';
import { twMerge } from 'tailwind-merge';
import tontines from '@/routes/tontines';
import sessions from '@/routes/tontines/sessions';
import type { Meeting, NavItem, Session, Tontine } from '@/types';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

export function formatCurrency(
    amount?: number | string | null,
    currency = 'XAF',
    locale = 'fr-FR',
): string {
    if (amount == null) {
        return '—';
    }

    const value = Number(amount);

    if (!Number.isFinite(value)) {
        return '—';
    }

    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
    }).format(value);
}

export function getTontineNavItems(tontine: Tontine): NavItem[] {
    return [
        {
            title: 'Vue d’ensemble',
            href: tontines.show(tontine.slug!),
            icon: LayoutDashboardIcon,
        },
        {
            title: 'Membres',
            href: tontines.memberships.index(tontine.slug!),
            icon: UsersIcon,
        },
        {
            title: 'Sessions',
            href: tontines.sessions.index(tontine.slug!),
            icon: CalendarDaysIcon,
        },
        {
            title: 'Règles de pénalité',
            href: tontines.penaltyRules.index({ tontine: tontine.slug! }),
            icon: ShieldCheckIcon,
        },
        {
            title: 'Rôles et permissions',
            href: tontines.roles.index({ tontine: tontine.slug! }),
            icon: UserRoundCogIcon,
        },
        {
            title: 'Finances',
            href: '#',
            icon: CircleDollarSignIcon,
        },
        {
            title: 'Rapports',
            href: '#',
            icon: ChartNoAxesColumnIcon,
        },
        {
            title: 'Paramètres',
            href: '#',
            icon: SettingsIcon,
        },
    ];
}

export function getSessionNavItems(
    tontine: Tontine,
    session: Session,
): NavItem[] {
    return [
        {
            title: 'Vue d’ensemble',
            href: sessions.show({
                tontine: tontine.slug!,
                session: session.slug,
            }),
            icon: LayoutDashboardIcon,
        },
        {
            title: 'Participants',
            href: tontines.sessions.participants.index({
                tontine: tontine.slug!,
                session: session.slug,
            }),
            icon: UsersIcon,
        },
        {
            title: 'Réunions',
            href: sessions.meetings.index({
                tontine: tontine.slug!,
                session: session.slug,
            }),
            icon: CalendarDaysIcon,
            activeWithParentUrl: true,
        },
        {
            title: 'Tirage',
            href: sessions.draw.show({
                tontine: tontine.slug!,
                session: session.slug,
            }),
            icon: ShuffleIcon,
        },
        {
            title: 'Dons',
            href: sessions.donations.index({
                tontine: tontine.slug!,
                session: session.slug,
            }),
            icon: HandHeartIcon,
        },
        {
            title: 'Transactions',
            href: sessions.transactions.index({
                tontine: tontine.slug!,
                session: session.slug,
            }),
            icon: ChartNoAxesColumnIcon,
        },
        {
            title: 'Prêts',
            href: sessions.loans.index({
                tontine: tontine.slug!,
                session: session.slug,
            }),
            icon: LandmarkIcon,
        },
        {
            title: 'Remboursements',
            href: sessions.repayments.index({
                tontine: tontine.slug!,
                session: session.slug,
            }),
            icon: ReceiptTextIcon,
        },
        {
            title: 'Assurance',
            href: sessions.insurance.index({
                tontine: tontine.slug!,
                session: session.slug,
            }),
            icon: PiggyBankIcon,
        },
    ];
}

export function getMeetingStatusLabel(status: Meeting['status']): string {
    switch (status) {
        case 'scheduled':
            return 'Prévue';

        case 'in_progress':
            return 'En cours';

        case 'completed':
            return 'Terminée';

        case 'cancelled':
            return 'Annulée';

        default:
            return '—';
    }
}
