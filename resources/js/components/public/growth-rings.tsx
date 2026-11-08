// Signature graphique du site : cernes du bois. Seul ornement, toujours discret (sprint 3).
const RINGS = [46, 40, 34.5, 29, 24, 19.5, 15, 11, 7.5];

export function GrowthRings({ className = '' }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 100 100"
            aria-hidden="true"
            className={className}
            fill="none"
            preserveAspectRatio="xMidYMid slice"
        >
            {RINGS.map((r, i) => (
                <ellipse
                    key={r}
                    cx={50 + i * 0.6}
                    cy={50 - i * 0.4}
                    rx={r}
                    ry={r * (0.93 + (i % 3) * 0.025)}
                    stroke="currentColor"
                    strokeWidth={i % 4 === 0 ? 0.6 : 0.35}
                />
            ))}
        </svg>
    );
}
