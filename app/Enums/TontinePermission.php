<?php

namespace App\Enums;

enum TontinePermission: string
{
    /*
    |--------------------------------------------------------------------------
    | Tontine
    |--------------------------------------------------------------------------
    */

    case ViewTontine = 'tontines.view';
    case UpdateTontine = 'tontines.update';
    case DeleteTontine = 'tontines.delete';
    case RestoreTontine = 'tontines.restore';
    case ForceDeleteTontine = 'tontines.force-delete';
    case ManageTontineSettings = 'tontines.settings.manage';

        /*
    |--------------------------------------------------------------------------
    | Membres
    |--------------------------------------------------------------------------
    */

    case ViewMemberships = 'memberships.view';
    case CreateMemberships = 'memberships.create';
    case UpdateMemberships = 'memberships.update';
    case DeleteMemberships = 'memberships.delete';
    case ReactivateMemberships = 'memberships.reactivate';
    case SuspendMemberships = 'memberships.suspend';
    case LeaveMemberships = 'memberships.leave';
    case AssignMembershipRoles = 'memberships.roles.assign';

        /*
    |--------------------------------------------------------------------------
    | Sessions
    |--------------------------------------------------------------------------
    */

    case ViewSessions = 'sessions.view';
    case CreateSessions = 'sessions.create';
    case UpdateSessions = 'sessions.update';
    case ActivateSessions = 'sessions.activate';
    case CloseSessions = 'sessions.close';
    case DeleteSessions = 'sessions.delete';

        /*
    |--------------------------------------------------------------------------
    | Participants aux sessions
    |--------------------------------------------------------------------------
    */

    case ViewSessionParticipants = 'session-participants.view';
    case CreateSessionParticipants = 'session-participants.create';
    case UpdateSessionParticipants = 'session-participants.update';
    case RemoveSessionParticipants = 'session-participants.remove';
    case ReactivateSessionParticipants = 'session-participants.reactivate';

        /*
    |--------------------------------------------------------------------------
    | Tirages
    |--------------------------------------------------------------------------
    */

    case ViewDraws = 'draws.view';
    case GenerateDraws = 'draws.generate';
    case ConfirmDraws = 'draws.confirm';
    case ResetDraws = 'draws.reset';
    case DeleteDraws = 'draws.delete';
    case RestoreDraws = 'draws.restore';

        // Meetings
    case ViewMeetings = 'meetings.view';
    case CreateMeetings = 'meetings.create';
    case UpdateMeetings = 'meetings.update';
    case OpenMeetings = 'meetings.open';
    case CloseMeetings = 'meetings.close';
    case CancelMeetings = 'meetings.cancel';
    case DeleteMeetings = 'meetings.delete';

        // Meeting agenda
    case ViewMeetingAgenda = 'meeting-agenda.view';
    case CreateMeetingAgenda = 'meeting-agenda.create';
    case UpdateMeetingAgenda = 'meeting-agenda.update';
    case DeleteMeetingAgenda = 'meeting-agenda.delete';

        // Attendances
    case ViewMeetingAttendances = 'meeting-attendances.view';
    case UpdateMeetingAttendances = 'meeting-attendances.update';

        // Notes
    case ViewMeetingNotes = 'meeting-notes.view';
    case CreateMeetingNotes = 'meeting-notes.create';
    case UpdateMeetingNotes = 'meeting-notes.update';
    case DeleteMeetingNotes = 'meeting-notes.delete';

        // Decisions
    case ViewMeetingDecisions = 'meeting-decisions.view';
    case CreateMeetingDecisions = 'meeting-decisions.create';
    case UpdateMeetingDecisions = 'meeting-decisions.update';
    case DeleteMeetingDecisions = 'meeting-decisions.delete';

        /*
    |--------------------------------------------------------------------------
    | Cotisations
    |--------------------------------------------------------------------------
    */

    case ViewContributions = 'contributions.view';
    case CreateContributions = 'contributions.create';
    case UpdateContributions = 'contributions.update';
    case DeleteContributions = 'contributions.delete';

        /*
    |--------------------------------------------------------------------------
    | Prêts
    |--------------------------------------------------------------------------
    */

    case ViewLoans = 'loans.view';
    case CreateLoans = 'loans.create';
    case ApproveLoans = 'loans.approve';
    case UpdateLoans = 'loans.update';
    case DeleteLoans = 'loans.delete';

        /*
    |--------------------------------------------------------------------------
    | Remboursements
    |--------------------------------------------------------------------------
    */

    case ViewRepayments = 'repayments.view';
    case CreateRepayments = 'repayments.create';
    case UpdateRepayments = 'repayments.update';
    case DeleteRepayments = 'repayments.delete';

        /*
    |--------------------------------------------------------------------------
    | Pénalités
    |--------------------------------------------------------------------------
    */

    case ViewPenalties = 'penalties.view';
    case CreatePenalties = 'penalties.create';
    case UpdatePenalties = 'penalties.update';
    case DeletePenalties = 'penalties.delete';

        /*
    |--------------------------------------------------------------------------
    | Fonds de caisse
    |--------------------------------------------------------------------------
    */

    case ViewCashFund = 'cash-fund.view';
    case ManageCashFund = 'cash-fund.manage';

        /*
    |--------------------------------------------------------------------------
    | Comptabilité
    |--------------------------------------------------------------------------
    */

    case ViewAccounting = 'accounting.view';
    case ExportAccounting = 'accounting.export';

        /*
    |--------------------------------------------------------------------------
    | Rapports
    |--------------------------------------------------------------------------
    */

    case ViewReports = 'reports.view';
    case ExportReports = 'reports.export';

    public function label(): string
    {
        return match ($this) {
            self::ViewTontine => __('Consulter la tontine'),
            self::UpdateTontine => __('Modifier la tontine'),
            self::DeleteTontine => __('Supprimer la tontine'),
            self::RestoreTontine => __('Restaurer la tontine'),
            self::ForceDeleteTontine => __('Supprimer définitivement la tontine'),
            self::ManageTontineSettings => __('Gérer les paramètres de la tontine'),

            self::ViewMemberships => __('Consulter les membres'),
            self::CreateMemberships => __('Ajouter des membres'),
            self::UpdateMemberships => __('Modifier les membres'),
            self::DeleteMemberships => __('Retirer des membres'),
            self::ReactivateMemberships => __('Réactiver les membres'),
            self::SuspendMemberships => __('Suspendre les membres'),
            self::LeaveMemberships => __('Enregistrer le départ des membres'),
            self::AssignMembershipRoles => __('Attribuer les rôles aux membres'),

            self::ViewSessions => __('Consulter les sessions'),
            self::CreateSessions => __('Créer des sessions'),
            self::UpdateSessions => __('Modifier les sessions'),
            self::ActivateSessions => __('Activer les sessions'),
            self::CloseSessions => __('Fermer les sessions'),
            self::DeleteSessions => __('Supprimer les sessions'),

            self::ViewSessionParticipants => __('Consulter les participants aux sessions'),
            self::CreateSessionParticipants => __('Ajouter des participants aux sessions'),
            self::UpdateSessionParticipants => __('Modifier les participants aux sessions'),
            self::RemoveSessionParticipants => __('Retirer des participants des sessions'),
            self::ReactivateSessionParticipants => __('Réactiver les participants aux sessions'),

            self::ViewDraws => __('Consulter les tirages'),
            self::GenerateDraws => __('Effectuer les tirages'),
            self::ConfirmDraws => __('Confirmer les tirages'),
            self::ResetDraws => __('Réinitialiser les tirages'),
            self::DeleteDraws => __('Supprimer les tirages'),

            self::ViewContributions => __('Consulter les cotisations'),
            self::CreateContributions => __('Enregistrer les cotisations'),
            self::UpdateContributions => __('Modifier les cotisations'),
            self::DeleteContributions => __('Supprimer les cotisations'),

            self::ViewLoans => __('Consulter les prêts'),
            self::CreateLoans => __('Créer les prêts'),
            self::ApproveLoans => __('Approuver les prêts'),
            self::UpdateLoans => __('Modifier les prêts'),
            self::DeleteLoans => __('Supprimer les prêts'),

            self::ViewRepayments => __('Consulter les remboursements'),
            self::CreateRepayments => __('Enregistrer les remboursements'),
            self::UpdateRepayments => __('Modifier les remboursements'),
            self::DeleteRepayments => __('Supprimer les remboursements'),

            self::ViewPenalties => __('Consulter les pénalités'),
            self::CreatePenalties => __('Créer les pénalités'),
            self::UpdatePenalties => __('Modifier les pénalités'),
            self::DeletePenalties => __('Supprimer les pénalités'),

            self::ViewCashFund => __('Consulter le fonds de caisse'),
            self::ManageCashFund => __('Gérer le fonds de caisse'),

            self::ViewAccounting => __('Consulter la comptabilité'),
            self::ExportAccounting => __('Exporter la comptabilité'),

            self::ViewReports => __('Consulter les rapports'),
            self::ExportReports => __('Exporter les rapports'),
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn(self $permission): string => $permission->value,
            self::cases(),
        );
    }
}
