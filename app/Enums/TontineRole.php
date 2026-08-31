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
        return array_map(
            fn (self $role) => [
                'label' => $role->label(),
                'value' => $role->value,
            ],
            self::cases(),
        );
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
                TontinePermission::ReactivateMemberships,
                TontinePermission::LeaveMemberships,

                TontinePermission::ViewSessions,
                TontinePermission::CreateSessions,
                TontinePermission::UpdateSessions,
                TontinePermission::ActivateSessions,
                TontinePermission::CloseSessions,

                TontinePermission::ViewSessionParticipants,
                TontinePermission::CreateSessionParticipants,
                TontinePermission::UpdateSessionParticipants,
                TontinePermission::RemoveSessionParticipants,
                TontinePermission::ReactivateSessionParticipants,

                TontinePermission::ViewDraws,
                TontinePermission::GenerateDraws,
                TontinePermission::ConfirmDraws,
                TontinePermission::ResetDraws,

                TontinePermission::ViewReports,
            ],

            self::Treasurer => [
                TontinePermission::ViewTontine,
                TontinePermission::ViewMemberships,

                TontinePermission::ViewSessions,
                TontinePermission::ViewSessionParticipants,

                TontinePermission::ViewDraws,

                TontinePermission::ViewContributions,
                TontinePermission::CreateContributions,
                TontinePermission::UpdateContributions,

                TontinePermission::ViewLoans,
                TontinePermission::CreateLoans,
                TontinePermission::ApproveLoans,
                TontinePermission::UpdateLoans,

                TontinePermission::ViewRepayments,
                TontinePermission::CreateRepayments,
                TontinePermission::UpdateRepayments,

                TontinePermission::ViewCashFund,
                TontinePermission::ManageCashFund,

                TontinePermission::ViewAccounting,
                TontinePermission::ExportAccounting,

                TontinePermission::ViewReports,
                TontinePermission::ExportReports,
                TontinePermission::ViewPayouts,
                TontinePermission::CreatePayouts,
                TontinePermission::UpdatePayouts,
                TontinePermission::PayPayouts,
                TontinePermission::CancelPayouts,

            ],

            self::Censor => [
                TontinePermission::ViewTontine,
                TontinePermission::ViewMemberships,

                TontinePermission::ViewSessions,
                TontinePermission::ViewSessionParticipants,

                TontinePermission::ViewDraws,

                TontinePermission::ViewPenalties,
                TontinePermission::CreatePenalties,
                TontinePermission::UpdatePenalties,

                TontinePermission::ViewReports,
            ],

            self::Auditor => [
                TontinePermission::ViewTontine,
                TontinePermission::ViewMemberships,

                TontinePermission::ViewSessions,
                TontinePermission::ViewSessionParticipants,

                TontinePermission::ViewDraws,

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
                TontinePermission::ViewSessionParticipants,

                TontinePermission::ViewDraws,

                TontinePermission::ViewContributions,
                TontinePermission::ViewLoans,
                TontinePermission::ViewRepayments,
                TontinePermission::ViewPenalties,
            ],
        };
    }
}
