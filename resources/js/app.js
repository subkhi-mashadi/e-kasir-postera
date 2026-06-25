import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';
import db from './db.js';
import { startSyncLoop, pendingCount, queueOrder, syncOutbox } from './sync.js';

Alpine.plugin(persist);
window.Alpine = Alpine;

window.__db          = db;
window.__pendingCount = pendingCount;
window.__queueOrder  = queueOrder;
window.__syncOutbox  = syncOutbox;

Alpine.start();

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.ready.then(() => {
        startSyncLoop(15000);
    });
} else {
    startSyncLoop(15000);
}
