import {
    CalendarRangeIcon,
    ChartNoAxesColumnIcon,
    FileTextIcon,
    HandHeartIcon,
    LandmarkIcon,
    ShieldCheckIcon,
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const features = [
    [
        CalendarRangeIcon,
        'Assises structurées',
        'Planifiez les rencontres, suivez l’ordre du jour, les présences, les notes et les décisions.',
    ],
    [
        ChartNoAxesColumnIcon,
        'Finances transparentes',
        'Gardez une vision claire des cotisations, versements, assurances et mouvements financiers.',
    ],
    [
        LandmarkIcon,
        'Prêts et remboursements',
        'Configurez les intérêts et échéances, puis suivez chaque remboursement sans perdre l’historique.',
    ],
    [
        HandHeartIcon,
        'Dons et solidarité',
        'Organisez les aides accordées aux membres avec un processus contrôlé et traçable.',
    ],
    [
        FileTextIcon,
        'Rapports d’assise',
        'Centralisez toutes les informations d’une assise dans un rapport complet et exploitable.',
    ],
    [
        ShieldCheckIcon,
        'Rôles et permissions',
        'Donnez à chaque responsable uniquement les accès nécessaires dans chaque réunion.',
    ],
];

export function FeatureGrid() {
    return (
        <section
            id="fonctionnalites"
            className="border-y bg-muted/30 py-20 sm:py-24"
        >
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-2xl text-center">
                    <p className="text-sm font-medium text-primary">
                        Une gestion complète
                    </p>
                    <h2 className="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                        Tout ce dont votre réunion a besoin, au même endroit
                    </h2>
                    <p className="mt-4 text-muted-foreground">
                        Des assises aux finances, chaque module partage la même
                        information et conserve un historique fiable.
                    </p>
                </div>
                <div className="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {features.map(([Icon, title, description]) => (
                        <Card
                            key={String(title)}
                            className="bg-background transition-shadow hover:shadow-md"
                        >
                            <CardHeader>
                                <span className="mb-2 grid size-10 place-items-center rounded-lg bg-primary/10 text-primary">
                                    <Icon className="size-5" />
                                </span>
                                <CardTitle className="text-lg">
                                    {String(title)}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm leading-6 text-muted-foreground">
                                    {String(description)}
                                </p>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
}
