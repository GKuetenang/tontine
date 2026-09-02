import { UsersIcon } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export function AttendancePlaceholder() {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Présences</CardTitle>
            </CardHeader>

            <CardContent>
                <div className="flex flex-col items-center justify-center gap-3 py-10 text-center">
                    <UsersIcon className="size-8 text-muted-foreground" />

                    <div className="space-y-1">
                        <p className="font-medium">
                            L’assise n’est pas encore ouverte
                        </p>

                        <p className="max-w-md text-sm text-muted-foreground">
                            Les présences seront initialisées automatiquement
                            lors de l’ouverture de l’assise.
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
