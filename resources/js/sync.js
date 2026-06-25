import db from './db.js';

let _syncing = false;

export async function syncOutbox() {
    if (_syncing) return;
    _syncing = true;

    try {
        const pending = await db.outbox.where('synced_at').equals('').toArray();
        if (!pending.length) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

        for (const item of pending) {
            try {
                const res = await fetch('/api/sync/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(item.payload),
                });

                if (res.ok) {
                    const data = await res.json();
                    await db.outbox.update(item.id, {
                        synced_at: new Date().toISOString(),
                        server_order_id: data.order_id,
                        invoice_no: data.invoice_no,
                    });
                    window.dispatchEvent(new CustomEvent('order-synced', { detail: { id: item.id, ...data } }));
                }
            } catch {
                // network error — will retry next sync
            }
        }
    } finally {
        _syncing = false;
    }
}

export async function queueOrder(payload) {
    const uuid = crypto.randomUUID();
    const id = await db.outbox.add({
        uuid,
        payload: { ...payload, uuid },
        synced_at: '',
        created_at: new Date().toISOString(),
    });
    return { id, uuid };
}

export async function pendingCount() {
    return db.outbox.where('synced_at').equals('').count();
}

export function startSyncLoop(intervalMs = 10000) {
    syncOutbox();
    return setInterval(() => {
        if (navigator.onLine) syncOutbox();
    }, intervalMs);
}
