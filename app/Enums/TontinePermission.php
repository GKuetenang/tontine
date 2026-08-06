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
    | Tirages
    |--------------------------------------------------------------------------
    */

    case ViewDraws = 'draws.view';
    case CreateDraws = 'draws.create';
    case UpdateDraws = 'draws.update';
    case DeleteDraws = 'draws.delete';

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
            self::AssignMembershipRoles => __('Attribuer les rôles aux membres'),

            self::ViewSessions => __('Consulter les sessions'),
            self::CreateSessions => __('Créer des sessions'),
            self::UpdateSessions => __('Modifier les sessions'),
            self::DeleteSessions => __('Supprimer les sessions'),
            self::ActivateSessions => __('Activer les sessions'),
            self::CloseSessions => __('Fermer les sessions'),

            self::ViewDraws => __('Consulter les tirages'),
            self::CreateDraws => __('Effectuer les tirages'),
            self::UpdateDraws => __('Modifier les tirages'),
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
