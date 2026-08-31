export function ContributionSummaryCard({
    title,
    value,
    icon: Icon,
}: {
    title: string;
    value: string | number;
    icon: React.ElementType;
}) {
    return (
        <div className="flex items-center gap-3 rounded-xl border p-4">
            <div className="rounded-full bg-primary/10 p-2 text-primary">
                <Icon className="size-5" />
            </div>

            <div>
                <p className="text-lg font-semibold">{value}</p>

                <p className="text-xs text-muted-foreground">{title}</p>
            </div>
        </div>
    );
}
