type Point = { date: string; views: number };

/**
 * F-DASH-03 (sprint 10) : vues par jour sur 30 jours.
 * Histogramme SVG sans bibliothèque ; le tableau caché est lu par les lecteurs d'écran.
 */
export function ViewsChart({ data, label }: { data: Point[]; label: string }) {
    const max = Math.max(1, ...data.map((d) => d.views));
    const barWidth = 100 / Math.max(1, data.length);

    return (
        <figure>
            <figcaption className="mb-3 text-sm font-medium">
                {label}
            </figcaption>
            <svg
                viewBox="0 0 100 40"
                preserveAspectRatio="none"
                className="h-40 w-full"
                role="img"
                aria-label={label}
            >
                {data.map((d, i) => {
                    const h = (d.views / max) * 36;

                    return (
                        <rect
                            key={d.date}
                            x={i * barWidth + barWidth * 0.15}
                            y={40 - h}
                            width={barWidth * 0.7}
                            height={h}
                            rx={0.6}
                            className="fill-legno-700"
                        >
                            <title>{`${d.date}: ${d.views}`}</title>
                        </rect>
                    );
                })}
            </svg>
            <div className="mt-1 flex justify-between text-xs text-muted-foreground">
                <span>{data[0]?.date}</span>
                <span>{data[data.length - 1]?.date}</span>
            </div>
            <table className="sr-only">
                <tbody>
                    {data.map((d) => (
                        <tr key={d.date}>
                            <td>{d.date}</td>
                            <td>{d.views}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </figure>
    );
}
