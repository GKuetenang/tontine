<?php

namespace App\Enums;

enum TontineRole: string
{
    case President = 'president';
    case Secretary = 'secretary';
    case Treasurer = 'treasurer';
    case Member = 'member';
    case Censor = 'censor';
    case Auditor = 'auditor';


    public static function getOptions(): array
    {
        return array_map(fn(self $status) => [
            'label' => $status->label(),
            'value' => $status->value,
        ], self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::President => __('Président'),
            self::Secretary => __('Secrétaire'),
            self::Treasurer => __('Trésorier'),
            self::Member => __('Membre'),
            self::Censor => __('Censeur'),
            self::Auditor => __('Commissaire aux comptes'),
        };
    }

    /**
     * @return list<TontinePermission>
     */
    public function defaultPermissions(): array
    {
        return match ($this) {
            self::President => TontinePermission::cases(),

            self::Secretary => [
                TontinePermission::ViewTontine,
                TontinePermission::ViewMemberships,
                TontinePermission::CreateMemberships,
                TontinePermission::UpdateMemberships,

                TontinePermission::ViewSessions,
                TontinePermission::CreateSessions,
                TontinePermission::UpdateSessions,

                TontinePermission::ViewDraws,
                TontinePermission::CreateDraws,

                TontinePermission::ViewReports,
            ],

            self::Treasurer => [
                TontinePermission::ViewTontine,
                TontinePermission::ViewMemberships,

                TontinePermission::ViewContributions,
                TontinePermission::CreateContributions,
                TontinePermission::UpdateContributions,

                TontinePermission::ViewLoans,
                TontinePermission::CreateLoans,
                TontinePermission::UpdateLoans,

                TontinePermission::ViewRepayments,
                TontinePermission::CreateRepayments,

                TontinePermission::ViewCashFund,
                TontinePermission::ManageCashFund,

                TontinePermission::ViewAccounting,
                TontinePermission::ExportAccounting,

                TontinePermission::ViewReports,
                TontinePermission::ExportReports,
            ],

            self::Censor => [
                TontinePermission::ViewTontine,
                TontinePermission::ViewMemberships,

                TontinePermission::ViewPenalties,
                TontinePermission::CreatePenalties,
                TontinePermission::UpdatePenalties,

                TontinePermission::ViewSessions,
                TontinePermission::ViewReports,
            ],

            self::Auditor => [
                TontinePermission::ViewTontine,
                TontinePermission::ViewMemberships,

                TontinePermission::ViewContributions,
                TontinePermission::ViewLoans,
                TontinePermission::ViewRepayments,
                TontinePermission::ViewPenalties,
                TontinePermission::ViewCashFund,

                TontinePermission::ViewAccounting,
                TontinePermission::ExportAccounting,

                TontinePermission::ViewReports,
                TontinePermission::ExportReports,
            ],

            self::Member => [
                TontinePermission::ViewTontine,
                TontinePermission::ViewMemberships,
                TontinePermission::ViewSessions,
                TontinePermission::ViewDraws,
                TontinePermission::ViewContributions,
                TontinePermission::ViewLoans,
                TontinePermission::ViewRepayments,
                TontinePermission::ViewPenalties,
            ],
        };
    }
}
