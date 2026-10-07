import type { BookingService, InitialStateType } from "./booking.reducer";
export function keyFor(shop: string, actor?: number): string;
export function readDraft(storage: Storage, key: string): InitialStateType | null;
export function writeDraft(storage: Storage, key: string, state: InitialStateType): void;
export function assignmentIds(services: BookingService[]): number[];