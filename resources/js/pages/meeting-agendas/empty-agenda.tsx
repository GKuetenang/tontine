import { ClipboardListIcon } from 'lucide-react';

export function EmptyAgenda() {
    return (
        <div className="flex flex-col items-center justify-center gap-3 py-10 text-center">
            <div className="rounded-full bg-muted p-3">
                <ClipboardListIcon className="size-6 text-muted-foreground" />
            </div>

            <div className="space-y-1">
                <p className="font-medium">Aucun point à l’ordre du jour</p>

                <p className="text-sm text-muted-foreground">
                    Ajoutez les sujets qui seront abordés pendant la réunion.
                </p>
            </div>
        </div>
    );
}
