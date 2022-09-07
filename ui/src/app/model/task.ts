import { Hashlist } from "./hashlist";

export interface NormalTask {
    id: number;
    name: string;
    priority: number;
    maxAgents: number;

    hashlistId: number;
    hashlist: Hashlist;
}