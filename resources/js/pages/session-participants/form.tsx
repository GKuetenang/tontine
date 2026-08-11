import { FormField } from "@/components/form-field"
import { Button } from "@/components/ui/button"
import { DateTimePicker } from "@/components/ui/datetime-picker"
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Spinner } from "@/components/ui/spinner"
import sessions from "@/routes/tontines/sessions"
import { Session } from "@/types"
import { Form } from "@inertiajs/react"
import { format, isValid, parseISO } from "date-fns"
import { SaveIcon } from "lucide-react"
import { ReactElement, useEffect, useState } from "react"
import { ResultTontine } from "."

type Props = {
    trigger: ReactElement,
    tontine: ResultTontine,
    session: Session;
}

function parseSessionDate(
    value?: string | null,
): Date | undefined {
    if (!value) {
        return undefined;
    }

    const date = parseISO(value);

    return isValid(date) ? date : undefined;
}

export function EditSessionForm({ trigger, tontine, session }: Props) {
    const [open, setOpen] = useState(false);

    const [startDate, setStartDate] = useState<Date | undefined>(
        parseSessionDate(session?.start_at),
    );

    const [endDate, setEndDate] = useState<Date | undefined>(
        parseSessionDate(session?.end_at),
    );

    useEffect(() => {
        if (!open) {
            return;
        }

        setStartDate(
            parseSessionDate(session?.start_at),
        );

        setEndDate(
            parseSessionDate(session?.end_at),
        );
    }, [
        open,
        session?.id,
        session?.start_at,
        session?.end_at,
    ]);


    const handleOpenChange = (value: boolean) => {
        setOpen(value);
    }

    const action = session.id ?
        sessions.update.form({ tontine: tontine.slug, session: session.slug }) :
        sessions.store.form({ tontine: tontine.slug })

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                {trigger}
            </DialogTrigger>
            <DialogContent
                className="sm:max-w-lg"
                onInteractOutside={(e) => e.preventDefault()}
                onEscapeKeyDown={(e) => e.preventDefault()}
            >
                <Form {...action}
                    resetOnSuccess
                    onSuccess={() => {
                        setOpen(false)
                    }}>
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>Ajouter une session</DialogTitle>
                                <DialogDescription>
                                    Ajouter une session de tontine
                                </DialogDescription>
                            </DialogHeader>

                            <FormField
                                error={errors['name']}
                                label="Nom"
                                htmlFor="name"
                                required
                            >
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={session?.name}
                                    aria-invalid={!!errors['name']}
                                />
                            </FormField>

                            <FormField
                                error={errors['default_contribution_amount']}
                                label="Montant par defaut"
                                htmlFor="default_contribution_amount"
                                optional
                            >
                                <Input
                                    id="default_contribution_amount"
                                    name="default_contribution_amount"
                                    defaultValue={session.default_contribution_amount ?? undefined}
                                    aria-invalid={!!errors['default_contribution_amount']}
                                />
                            </FormField>

                            <FormField
                                error={errors['start_at']}
                                label="Date de début"
                                htmlFor="start_at"
                            >
                                <input
                                    type="hidden"
                                    name="start_at"
                                    value={
                                        startDate
                                            ? format(
                                                startDate,
                                                'yyyy-MM-dd HH:mm:ss',
                                            )
                                            : ''
                                    }
                                />
                                <DateTimePicker
                                    granularity="minute"
                                    className="text-foreground"
                                    placeholder="Choisir une date"
                                    value={startDate}
                                    onChange={setStartDate}
                                />
                            </FormField>

                            <FormField
                                error={errors['end_at']}
                                label="Date de fin"
                                htmlFor="end_at"
                            >
                                <DateTimePicker
                                    granularity="minute"
                                    className="text-foreground"
                                    placeholder="Choisir une date"
                                    value={endDate}
                                    onChange={setEndDate}
                                />
                                <input
                                    type="hidden"
                                    name="end_at"
                                    value={
                                        endDate
                                            ? format(
                                                endDate,
                                                'yyyy-MM-dd HH:mm:ss',
                                            )
                                            : ''
                                    }
                                />
                            </FormField>


                            <DialogFooter>
                                <DialogClose asChild><Button variant="outline">Cancel</Button></DialogClose>
                                <Button
                                    type="submit"
                                    tabIndex={4}
                                    disabled={processing}
                                    data-test="login-button"
                                >
                                    {processing ? <Spinner /> : <SaveIcon />}
                                    Enregistrer
                                </Button>
                            </DialogFooter>
                        </div>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    )
}
