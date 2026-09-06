import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

import './booking';

Alpine.plugin(collapse);

window.Alpine = Alpine;

Alpine.start();
