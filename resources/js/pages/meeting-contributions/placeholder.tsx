import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import { CoinsIcon } from 'lucide-react';

export function ContributionPlaceholder() {
    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    Cotisations
                </CardTitle>
            </CardHeader>

            <CardContent>
                <div className="flex flex-col items-center justify-center gap-3 py-10 text-center">
                    <div className="rounded-full bg-muted p-3">
                        <CoinsIcon className="size-6 text-muted-foreground" />
                    </div>

                    <div className="space-y-1">
                        <p className="font-medium">
                            La réunion n’est pas encore ouverte
                        </p>

                        <p className="max-w-md text-sm text-muted-foreground">
                            Les cotisations seront générées automatiquement
                            pour les participants actifs lors de l’ouverture
                            de la réunion.
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}