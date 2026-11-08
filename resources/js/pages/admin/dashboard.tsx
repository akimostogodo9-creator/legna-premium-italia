import { Head, Link, usePage } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import { AlertCircle, AlertTriangle, CheckCircle2, Info } from 'lucide-react';
import type { ReactNode } from 'react';
import { ViewsChart } from '@/components/admin/views-chart';
import { useTranslations } from '@/lib/i18n';
import messageRoutes from '@/routes/admin/messages';
import productRoutes from '@/routes/admin/products';

type Totals = { views: number; order_clicks: number; whatsapp_clicks: number };
type Alert = {
    key: string;
    level: 'critical' | 'warning' | 'info';
    message: string;
    link: string;
};
type Props = {
    products: { total: number; active: number; hidden: number };
    unread: number;
    period7: Totals;
    period30: Totals;
    topProducts: {
        id: number;
        name: string;
        views: number;
        requests: number;
    }[];
    daily: { date: string; views: number }[];
    latestMessages: {
        id: number;
        name: string;
        subject: string;
        status: string;
        received: string;
    }[];
    alerts: Alert[];
};

const alertStyle = {
    critical: {
        icon: AlertCircle,
        cls: 'border-red-200 bg-red-50 text-red-900',
    },
    warning: {
        icon: AlertTriangle,
        cls: 'border-amber-200 bg-amber-50 text-amber-900',
    },
    info: { icon: Info, cls: 'border-sky-200 bg-sky-50 text-sky-900' },
} as const;

// F-DASH-01→04 (sprint 10) — aucun libellé de commande ni de chiffre d'affaires (R-STAT-05)
export default function Dashboard(props: Props) {
    const t = useTranslations();
    const { auth } = usePage().props;

    const stat = (
        label: string,
        value: number,
        href: NonNullable<InertiaLinkProps['href']>,
    ): ReactNode => (
        <Link href={href} className="rounded-lg border p-4 hover:bg-muted/40">
            <p className="text-sm text-muted-foreground">{label}</p>
            <p className="mt-1 text-3xl font-semibold tabular-nums">{value}</p>
        </Link>
    );

    const period = (title: string, totals: Totals) => (
        <div className="rounded-lg border p-4">
            <p className="font-medium">{title}</p>
            <dl className="mt-3 grid gap-2 text-sm">
                {(['views', 'order_clicks', 'whatsapp_clicks'] as const).map(
                    (key) => (
                        <div key={key} className="flex justify-between gap-4">
                            <dt className="text-muted-foreground">
                                {t(`dashboard.${key}`)}
                            </dt>
                            <dd className="font-semibold tabular-nums">
                                {totals[key]}
                            </dd>
                        </div>
                    ),
                )}
            </dl>
        </div>
    );

    return (
        <>
            <Head title={t('dashboard.title')} />
            <div className="flex flex-col gap-8 p-4 md:p-8">
                <h1 className="text-2xl font-semibold">
                    {t('dashboard.welcome', { name: auth.user?.name ?? '' })}
                </h1>

                <section aria-labelledby="alerts-title">
                    <h2
                        id="alerts-title"
                        className="mb-3 text-lg font-semibold"
                    >
                        {t('dashboard.alerts')}
                    </h2>
                    {props.alerts.length === 0 ? (
                        <p className="flex items-center gap-2 rounded-md border border-green-200 bg-green-50 p-3 text-green-900">
                            <CheckCircle2 className="size-5" />{' '}
                            {t('dashboard.alerts_ok')}
                        </p>
                    ) : (
                        <ul className="grid gap-2">
                            {props.alerts.map((alert) => {
                                const { icon: Icon, cls } =
                                    alertStyle[alert.level];

                                return (
                                    <li
                                        key={alert.key}
                                        className={`flex items-center gap-3 rounded-md border p-3 text-sm ${cls}`}
                                    >
                                        <Icon
                                            className="size-5 shrink-0"
                                            aria-hidden="true"
                                        />
                                        <span className="min-w-0 flex-1">
                                            {alert.message}
                                        </span>
                                        <Link
                                            href={alert.link}
                                            className="shrink-0 font-medium underline underline-offset-2"
                                        >
                                            {t('dashboard.fix')}
                                        </Link>
                                    </li>
                                );
                            })}
                        </ul>
                    )}
                </section>

                <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {stat(
                        t('dashboard.products_total'),
                        props.products.total,
                        productRoutes.index(),
                    )}
                    {stat(
                        t('dashboard.products_active'),
                        props.products.active,
                        productRoutes.index({ query: { status: 'active' } }),
                    )}
                    {stat(
                        t('dashboard.products_hidden'),
                        props.products.hidden,
                        productRoutes.index({ query: { status: 'inactive' } }),
                    )}
                    {stat(
                        t('dashboard.unread'),
                        props.unread,
                        messageRoutes.index({ query: { status: 'new' } }),
                    )}
                </section>

                <section className="grid gap-4 lg:grid-cols-2">
                    {period(t('dashboard.period_7'), props.period7)}
                    {period(t('dashboard.period_30'), props.period30)}
                    <p className="text-sm text-muted-foreground lg:col-span-2">
                        {t('dashboard.requests_hint')}
                    </p>
                </section>

                <section className="grid gap-8 lg:grid-cols-2">
                    <div className="rounded-lg border p-4">
                        <ViewsChart
                            data={props.daily}
                            label={t('dashboard.chart')}
                        />
                    </div>
                    <div className="rounded-lg border p-4">
                        <h2 className="text-sm font-medium">
                            {t('dashboard.top_products')}
                        </h2>
                        <p className="mb-3 text-xs text-muted-foreground">
                            {t('dashboard.top_legend')}
                        </p>
                        {props.topProducts.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                {t('dashboard.no_data')}
                            </p>
                        ) : (
                            <ol className="grid gap-2 text-sm">
                                {props.topProducts.map((p) => (
                                    <li
                                        key={p.id}
                                        className="flex justify-between gap-4"
                                    >
                                        <Link
                                            href={productRoutes.edit(p.id)}
                                            className="truncate hover:underline"
                                            translate="no"
                                        >
                                            {p.name}
                                        </Link>
                                        <span className="shrink-0 text-muted-foreground tabular-nums">
                                            {p.views} · {p.requests}
                                        </span>
                                    </li>
                                ))}
                            </ol>
                        )}
                    </div>
                </section>

                <section className="rounded-lg border p-4">
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-sm font-medium">
                            {t('dashboard.latest_messages')}
                        </h2>
                        <Link
                            href={messageRoutes.index()}
                            className="text-sm underline underline-offset-2"
                        >
                            {t('dashboard.see_all')}
                        </Link>
                    </div>
                    {props.latestMessages.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            {t('dashboard.no_data')}
                        </p>
                    ) : (
                        <ul className="divide-y text-sm">
                            {props.latestMessages.map((m) => (
                                <li key={m.id}>
                                    <Link
                                        href={messageRoutes.show(m.id)}
                                        className="flex justify-between gap-4 py-2 hover:underline"
                                    >
                                        <span
                                            className={`truncate ${m.status === 'new' ? 'font-semibold' : ''}`}
                                            translate="no"
                                        >
                                            {m.name} — {m.subject}
                                        </span>
                                        <span className="shrink-0 text-muted-foreground">
                                            {m.received}
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </>
    );
}
