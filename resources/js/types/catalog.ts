// Données du catalogue envoyées par les Resources Laravel (sprint 5)
export type ImageData = {
    src: string;
    srcset: string;
    width: number | null;
    height: number | null;
    alt: string;
};

export type ProductCard = {
    id: number;
    name: string;
    slug: string;
    url: string;
    short_description: string | null;
    category: { name: string; slug: string } | null;
    price_label: string | null;
    unit: string | null;
    price_on_request: boolean;
    availability: {
        value: 'available' | 'on_order' | 'out_of_stock';
        label: string;
    };
    image: ImageData | null;
};

export type ProductDetail = ProductCard & {
    description: string | null;
    images: ImageData[];
    specs: { key: string; value: string }[];
    is_active: boolean;
    meta_title: string | null;
    meta_description: string | null;
};

export type CategoryData = {
    name: string;
    slug: string;
    url: string;
    description: string | null;
    image: string | null;
};

export type Paginated<T> = {
    data: T[];
    total: number;
    current_page: number;
    last_page: number;
    prev_url: string | null;
    next_url: string | null;
    pages: { page: number; url: string }[];
};
