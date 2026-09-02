import { Form } from '@inertiajs/react';
import { SaveIcon } from 'lucide-react';
import type { ReactElement } from 'react';
import { useState } from 'react';
import { FormField } from '@/components/form-field';
import type { SelectOption } from '@/components/select-with-items';
import { SelectWithItems } from '@/components/select-with-items';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import penaltyRules from '@/routes/groups/penalty-rules';
import type { PenaltyRule, Group } from '@/types';

type Props = {
    group: Group;
    rule?: PenaltyRule;
    trigger: ReactElement;
    triggers: SelectOption[];
    calculationTypes: SelectOption[];
    graceUnits: SelectOption[];
};

export function PenaltyRuleForm({
    group,
    rule,
    trigger,
    triggers,
    calculationTypes,
    graceUnits,
}: Props) {
    const [open, setOpen] = useState(false);
    const [automatic, setAutomatic] = useState(rule?.is_automatic ?? false);
    const [active, setActive] = useState(rule?.is_active ?? false);
    const suffix = String(rule?.id ?? 'new');
    const action = rule
        ? penaltyRules.update.form({
              group: group.slug!,
              penalty_rule: rule.id,
          })
        : penaltyRules.store.form({ group: group.slug! });

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent className="sm:max-w-2xl">
                <Form {...action} onSuccess={() => setOpen(false)}>
                    {({ errors, processing }) => (
                        <div className="space-y-5">
                            <DialogHeader>
                                <DialogTitle>
                                    {rule
                                        ? 'Modifier la règle'
                                        : 'Ajouter une règle'}
                                </DialogTitle>
                                <DialogDescription>
                                    Les modifications s’appliquent directement
                                    aux prochaines pénalités de la réunion.
                                </DialogDescription>
                            </DialogHeader>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <FormField
                                    label="Nom"
                                    htmlFor={'name-' + suffix}
                                    error={errors['name']}
                                    required
                                >
                                    <Input
                                        id={'name-' + suffix}
                                        name="name"
                                        defaultValue={rule?.name}
                                    />
                                </FormField>
                                <FormField
                                    label="Déclencheur"
                                    htmlFor={'trigger-' + suffix}
                                    error={errors['trigger']}
                                    required
                                >
                                    <SelectWithItems
                                        id={'trigger-' + suffix}
                                        name="trigger"
                                        items={triggers}
                                        defaultValue={rule?.trigger ?? 'manual'}
                                    />
                                </FormField>
                                <FormField
                                    label="Calcul"
                                    htmlFor={'calculation-' + suffix}
                                    error={errors['calculation_type']}
                                    required
                                >
                                    <SelectWithItems
                                        id={'calculation-' + suffix}
                                        name="calculation_type"
                                        items={calculationTypes}
                                        defaultValue={
                                            rule?.calculation_type ?? 'fixed'
                                        }
                                    />
                                </FormField>
                                <FormField
                                    label="Valeur"
                                    htmlFor={'value-' + suffix}
                                    error={errors['value']}
                                    required={active}
                                >
                                    <Input
                                        id={'value-' + suffix}
                                        name="value"
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        defaultValue={rule?.value ?? ''}
                                    />
                                </FormField>
                                <FormField
                                    label="Délai de tolérance"
                                    htmlFor={'grace-' + suffix}
                                    error={errors['grace_period']}
                                    optional
                                >
                                    <Input
                                        id={'grace-' + suffix}
                                        name="grace_period"
                                        type="number"
                                        min={0}
                                        defaultValue={rule?.grace_period ?? ''}
                                    />
                                </FormField>
                                <FormField
                                    label="Unité du délai"
                                    htmlFor={'unit-' + suffix}
                                    error={errors['grace_unit']}
                                    optional
                                >
                                    <SelectWithItems
                                        id={'unit-' + suffix}
                                        name="grace_unit"
                                        items={graceUnits}
                                        defaultValue={
                                            rule?.grace_unit ?? 'minutes'
                                        }
                                    />
                                </FormField>
                            </div>
                            <div className="grid gap-3 rounded-lg border p-4 sm:grid-cols-2">
                                <Label className="flex items-center gap-3">
                                    <Checkbox
                                        checked={automatic}
                                        onCheckedChange={(checked) =>
                                            setAutomatic(checked === true)
                                        }
                                    />
                                    Application automatique
                                </Label>
                                <input
                                    type="hidden"
                                    name="is_automatic"
                                    value={automatic ? '1' : '0'}
                                />
                                <Label className="flex items-center gap-3">
                                    <Checkbox
                                        checked={active}
                                        onCheckedChange={(checked) =>
                                            setActive(checked === true)
                                        }
                                    />
                                    Règle active
                                </Label>
                                <input
                                    type="hidden"
                                    name="is_active"
                                    value={active ? '1' : '0'}
                                />
                            </div>
                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Annuler
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    {processing ? <Spinner /> : <SaveIcon />}{' '}
                                    Enregistrer
                                </Button>
                            </DialogFooter>
                        </div>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
