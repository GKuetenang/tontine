<?php

namespace App\Enums;

enum GroupRole: string
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
     * @return list<GroupPermission>
     */
    public function defaultPermissions(): array
    {
        return match ($this) {
            self::President => GroupPermission::cases(),

            self::Secretary => [
                GroupPermission::ViewGroup,

                GroupPermission::ViewMemberships,
                GroupPermission::CreateMemberships,
                GroupPermission::UpdateMemberships,
                GroupPermission::ReactivateMemberships,
                GroupPermission::LeaveMemberships,

                GroupPermission::ViewSessions,
                GroupPermission::CreateSessions,
                GroupPermission::UpdateSessions,
                GroupPermission::ActivateSessions,
                GroupPermission::CloseSessions,

                GroupPermission::ViewSessionParticipants,
                GroupPermission::CreateSessionParticipants,
                GroupPermission::UpdateSessionParticipants,
                GroupPermission::RemoveSessionParticipants,
                GroupPermission::ReactivateSessionParticipants,

                GroupPermission::ViewDraws,
                GroupPermission::GenerateDraws,
                GroupPermission::ConfirmDraws,
                GroupPermission::ResetDraws,

                GroupPermission::ViewReports,
            ],

            self::Treasurer => [
                GroupPermission::ViewGroup,
                GroupPermission::ViewMemberships,

                GroupPermission::ViewSessions,
                GroupPermission::ViewSessionParticipants,

                GroupPermission::ViewDraws,

                GroupPermission::ViewContributions,
                GroupPermission::CreateContributions,
                GroupPermission::UpdateContributions,

                GroupPermission::ViewDonations,
                GroupPermission::CreateDonations,
                GroupPermission::PayDonations,
                GroupPermission::CancelDonations,

                GroupPermission::ViewLoans,
                GroupPermission::CreateLoans,
                GroupPermission::ApproveLoans,
                GroupPermission::UpdateLoans,

                GroupPermission::ViewRepayments,
                GroupPermission::CreateRepayments,
                GroupPermission::UpdateRepayments,

                GroupPermission::ViewInsurance,
                GroupPermission::ManageInsurance,

                GroupPermission::ViewAccounting,
                GroupPermission::ExportAccounting,

                GroupPermission::ViewReports,
                GroupPermission::ExportReports,
                GroupPermission::ViewPayouts,
                GroupPermission::CreatePayouts,
                GroupPermission::UpdatePayouts,
                GroupPermission::PayPayouts,
                GroupPermission::CancelPayouts,

            ],

            self::Censor => [
                GroupPermission::ViewGroup,
                GroupPermission::ViewMemberships,

                GroupPermission::ViewSessions,
                GroupPermission::ViewSessionParticipants,

                GroupPermission::ViewDraws,

                GroupPermission::ViewPenalties,
                GroupPermission::CreatePenalties,
                GroupPermission::UpdatePenalties,

                GroupPermission::ViewReports,
            ],

            self::Auditor => [
                GroupPermission::ViewGroup,
                GroupPermission::ViewMemberships,

                GroupPermission::ViewSessions,
                GroupPermission::ViewSessionParticipants,

                GroupPermission::ViewDraws,

                GroupPermission::ViewContributions,
                GroupPermission::ViewLoans,
                GroupPermission::ViewRepayments,
                GroupPermission::ViewPenalties,
                GroupPermission::ViewInsurance,

                GroupPermission::ViewAccounting,
                GroupPermission::ExportAccounting,

                GroupPermission::ViewReports,
                GroupPermission::ExportReports,
            ],

            self::Member => [
                GroupPermission::ViewGroup,
                GroupPermission::ViewMemberships,

                GroupPermission::ViewSessions,
                GroupPermission::ViewSessionParticipants,

                GroupPermission::ViewDraws,

                GroupPermission::ViewContributions,
                GroupPermission::ViewLoans,
                GroupPermission::ViewRepayments,
                GroupPermission::ViewPenalties,
            ],
        };
    }
}
