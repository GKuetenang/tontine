import { Card, CardContent } from "../ui/card";

export function SummaryCard({
    title,
    value,
    icon: Icon,
}: {
    title: string;
    value: string | number;
    icon: React.ElementType;
}) {
    return (
        <Card>
            <CardContent className="flex items-start justify-between ">
                <div className="space-y-1">
                    <p className="text-sm text-muted-foreground">
                        {title}
                    </p>

                    <p className="text-lg font-semibold">
                        {value}
                    </p>
                </div>

                <div className="rounded-lg bg-primary/10 p-2 text-primary">
                    <Icon className="size-5" />
                </div>
            </CardContent>
        </Card>
    );
}