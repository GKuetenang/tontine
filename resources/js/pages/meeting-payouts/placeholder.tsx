import { HandCoinsIcon } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type Props = {
    message?: string;
};

export function MeetingPayoutsPlaceholder({
    message = 'Les versements pourront être enregistrés lorsque l’assise sera en cours.',
}: Props) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Versements</CardTitle>
            </CardHeader>

            <CardContent>
                <div className="flex flex-col items-center justify-center gap-3 py-10 text-center">
                    <div className="rounded-full bg-muted p-3">
                        <HandCoinsIcon className="size-6 text-muted-foreground" />
                    </div>

                    <div className="space-y-1">
                        <p className="font-medium">
                            Aucun versement disponible
                        </p>

                        <p className="max-w-md text-sm text-muted-foreground">
                            {message}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
