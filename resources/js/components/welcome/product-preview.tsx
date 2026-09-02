import {
    CalendarDaysIcon,
    CircleDollarSignIcon,
    UsersIcon,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export function ProductPreview() {
    return (
        <div className="relative mx-auto w-full max-w-2xl lg:mx-0">
            <div className="absolute -inset-8 -z-10 rounded-full bg-primary/10 blur-3xl" />
            <Card className="overflow-hidden border-border/80 bg-background/95 pt-0 shadow-2xl shadow-primary/10">
                <CardHeader className="flex-row items-center justify-between border-b bg-muted/30 py-4">
                    <div>
                        <p className="text-xs text-muted-foreground">
                            Vue d’ensemble
                        </p>
                        <CardTitle className="mt-1 text-base">
                            Association Espoir
                        </CardTitle>
                    </div>
                    <Badge className="bg-emerald-500/10 text-emerald-700 hover:bg-emerald-500/10 dark:text-emerald-400">
                        Session active
                    </Badge>
                </CardHeader>
                <CardContent className="space-y-5 p-5 sm:p-6">
                    <div className="grid gap-3 sm:grid-cols-3">
                        {[
                            [UsersIcon, 'Membres', '24'],
                            [CalendarDaysIcon, 'Prochaine assise', '08 sept.'],
                            [CircleDollarSignIcon, 'Solde', '1 485 000 XAF'],
                        ].map(([Icon, label, value]) => (
                            <div
                                key={String(label)}
                                className="rounded-xl border bg-card p-4"
                            >
                                <Icon className="mb-3 size-4 text-primary" />
                                <p className="text-xs text-muted-foreground">
                                    {String(label)}
                                </p>
                                <p className="mt-1 font-semibold">
                                    {String(value)}
                                </p>
                            </div>
                        ))}
                    </div>

                    <div className="grid gap-4 sm:grid-cols-[1.1fr_0.9fr]">
                        <div className="rounded-xl border p-4">
                            <div className="mb-4 flex items-center justify-between">
                                <p className="text-sm font-medium">
                                    Cotisations de l’assise
                                </p>
                                <span className="text-xs text-muted-foreground">
                                    18 / 24
                                </span>
                            </div>
                            <div className="h-2 overflow-hidden rounded-full bg-secondary">
                                <div className="h-full w-3/4 rounded-full bg-primary" />
                            </div>
                            <div className="mt-5 space-y-3">
                                {[
                                    'Cotisation enregistrée',
                                    'Remboursement reçu',
                                    'Versement effectué',
                                ].map((label, index) => (
                                    <div
                                        key={label}
                                        className="flex items-center gap-3 text-xs"
                                    >
                                        <span
                                            className={`size-2 rounded-full ${index === 2 ? 'bg-amber-500' : 'bg-emerald-500'}`}
                                        />
                                        <span className="flex-1 text-muted-foreground">
                                            {label}
                                        </span>
                                        <span>
                                            {index === 0
                                                ? '+50 000'
                                                : index === 1
                                                  ? '+25 000'
                                                  : '−400 000'}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className="rounded-xl border bg-primary p-4 text-primary-foreground">
                            <p className="text-xs text-primary-foreground/70">
                                À faire aujourd’hui
                            </p>
                            <p className="mt-2 text-xl font-semibold">
                                3 actions
                            </p>
                            <div className="mt-5 space-y-2 text-xs">
                                <p className="rounded-md bg-primary-foreground/10 p-2.5">
                                    Valider un prêt
                                </p>
                                <p className="rounded-md bg-primary-foreground/10 p-2.5">
                                    Compléter les présences
                                </p>
                                <p className="rounded-md bg-primary-foreground/10 p-2.5">
                                    Préparer l’ordre du jour
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
