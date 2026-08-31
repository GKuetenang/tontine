export function EmptySection({
    icon: Icon,
    title,
    description,
}: {
    icon: React.ElementType;
    title: string;
    description: string;
}) {
    return (
        <div className="flex flex-col items-center justify-center gap-3 py-10 text-center">
            <Icon className="size-8 text-muted-foreground" />

            <div className="space-y-1">
                <p className="font-medium">{title}</p>

                <p className="max-w-md text-sm text-muted-foreground">
                    {description}
                </p>
            </div>
        </div>
    );
}
