export interface BaseHashlist {
    name: string;
    format: number;
    hashtypeId: number;
    saltSeparator: string;
    isSecret: boolean;
    isHexSalted: boolean;
    isSalted: boolean;
    accessGroupId: number;
    useBrain: boolean;
    brainFeatures: number;
}

export interface CreateHashlist extends BaseHashlist {
    dataSourceType: string;
    dataSource: string;
}

export interface Hashlist extends BaseHashlist {
    id: number;
    hashCount: number;
    crackedCount: number;
    notes: string;
}