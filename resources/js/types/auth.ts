// Admin connecté : seulement les champs partagés par HandleInertiaRequests (sprint 2)
export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
};

export type Auth = {
    user: User | null;
};
