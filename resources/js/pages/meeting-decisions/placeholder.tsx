import { CheckCircle2Icon } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export function MeetingDecisionsPlaceholder() {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Décisions</CardTitle>
            </CardHeader>

            <CardContent>
                <div className="flex flex-col items-center justify-center gap-3 py-10 text-center">
                    <div className="rounded-full bg-muted p-3">
                        <CheckCircle2Icon className="size-6 text-muted-foreground" />
                    </div>

                    <div className="space-y-1">
                        <p className="font-medium">
                            L’assise n’est pas encore ouverte
                        </p>

                        <p className="max-w-md text-sm text-muted-foreground">
                            Les décisions pourront être enregistrées lorsque la
                            assise sera en cours.
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
