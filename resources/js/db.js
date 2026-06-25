import Dexie from 'dexie';

const db = new Dexie('ekasir_pos');

db.version(1).stores({
    products:   '++id, category_id, name',
    categories: '++id, name',
    outbox:     '++id, uuid, synced_at',
    settings:   'key',
});

export default db;
