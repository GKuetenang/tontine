import type { LucideProps } from "lucide-react";
import type { ForwardRefExoticComponent, RefAttributes } from "react";
import { Card, CardContent } from "./ui/card";

type Props = {
    title: string;
    value: string | number;
    detail?: string;
    icon: ForwardRefExoticComponent<Omit<LucideProps, "ref"> & RefAttributes<SVGSVGElement>>
}

export default function DashboardCard({ title, value, detail, icon: Icon }: Props) {
    return (
        <Card>
            <CardContent className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-sm text-muted-foreground">
                        {title}
                    </p>
                    <p className="mt-1 text-2xl font-semibold">
                        {value}
                    </p>
                    <p className="mt-1 text-xs text-muted-foreground">
                        {detail}
                    </p>
                </div>
                <div className="rounded-lg bg-primary/10 p-2 text-primary">
                    <Icon className="size-5" />
                </div>
            </CardContent>
        </Card>
    )
}
