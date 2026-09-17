import { CoinsIcon } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useTranslation } from '@/hooks/use-translation';

export function ContributionPlaceholder() {
    const { t } = useTranslation();

    return (
        <Card>
            <CardHeader>
                <CardTitle>{t('Cotisations')}</CardTitle>
            </CardHeader>

            <CardContent>
                <div className="flex flex-col items-center justify-center gap-3 py-10 text-center">
                    <div className="rounded-full bg-muted p-3">
                        <CoinsIcon className="size-6 text-muted-foreground" />
                    </div>

                    <div className="space-y-1">
                        <p className="font-medium">
                            {t('L’assise n’est pas encore ouverte')}
                        </p>

                        <p className="max-w-md text-sm text-muted-foreground">
                            {t(
                                'Les cotisations seront générées automatiquement pour les participants actifs lors de l’ouverture de la assise.',
                            )}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
