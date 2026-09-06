import Alpine from 'alpinejs';

const HAIR_LENGTHS = [
    { value: 'courts', label: 'Cheveux courts' },
    { value: 'mi-longs', label: 'Cheveux mi-longs' },
    { value: 'longs', label: 'Cheveux longs' },
];

const HAIR_COLORS = [
    { value: 'naturelle', label: 'Naturelle' },
    { value: 'coloration', label: 'Colorés' },
    { value: 'decoloration-balayage', label: 'Méchés / Balayage' },
    { value: 'decoloration', label: 'Décolorés' },
    { value: 'autre', label: 'Autre' },
];

const HAIR_LENGTH_LABELS = Object.fromEntries(HAIR_LENGTHS.map((o) => [o.value, o.label]));
const HAIR_COLOR_LABELS = Object.fromEntries(HAIR_COLORS.map((o) => [o.value, o.label]));

const WEEKDAY_LABELS = ['LUN', 'MAR', 'MER', 'JEU', 'VEN', 'SAM', 'DIM'];

function currentMonthKey() {
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
}

function money(value) {
    if (value === null || value === undefined) {
        return '';
    }

    const n = Number(value);
    const formatted = Number.isInteger(n) ? String(n) : n.toFixed(2).replace('.', ',');

    return `${formatted} €`;
}

document.addEventListener('alpine:init', () => {
    Alpine.data('bookingFlow', (services) => ({
        // --- Données de référence --------------------------------------------------------
        services,
        weekdayLabels: WEEKDAY_LABELS,
        hairLengths: HAIR_LENGTHS,
        hairColors: HAIR_COLORS,

        // --- Prestation ---------------------------------------------------------------------
        selectedServiceId: null,

        // --- Calendrier -------------------------------------------------------------------
        calendarMonth: currentMonthKey(),
        calendarDays: [],
        calendarLoading: false,
        calendarError: null,
        minDate: null,
        maxDate: null,

        // --- Créneau ----------------------------------------------------------------------
        selectedDate: null,
        slots: [],
        slotsLoading: false,
        slotsError: null,
        selectedSlot: null,

        // --- Cheveux ------------------------------------------------------------------------
        hairLength: '',
        hairColor: '',
        hairColorDetails: '',

        // --- Coordonnées ----------------------------------------------------------------------
        profile: {
            first_name: '',
            last_name: '',
            phone: '',
            email: '',
            message: '',
        },
        errors: {},

        // --- Soumission -----------------------------------------------------------------------
        submitting: false,
        submitError: null,

        // === Prestation sélectionnée (objet complet) ============================================
        get selectedService() {
            return this.services.find((s) => s.id === this.selectedServiceId) || null;
        },

        // Prix réellement applicable : dépend de la longueur des cheveux
        // quand la prestation est tarifée par longueur, sinon prix forfaitaire.
        // null tant que la longueur n'est pas choisie pour une prestation
        // tarifée par longueur.
        get effectivePrice() {
            const s = this.selectedService;
            if (!s) {
                return null;
            }
            if (s.has_length_pricing) {
                if (!this.hairLength) {
                    return null;
                }
                const p = s.length_prices[this.hairLength];
                return p === undefined || p === null ? s.price : p;
            }

            return s.price;
        },

        get totalPriceLabel() {
            const s = this.selectedService;
            if (!s) {
                return '';
            }
            if (s.is_on_quote) {
                return 'Sur devis';
            }
            if (this.effectivePrice === null) {
                return s.price_label; // "À partir de 80 €"
            }

            return money(this.effectivePrice);
        },

        get remainingAmountLabel() {
            const s = this.selectedService;
            if (!s) {
                return '';
            }
            if (s.is_on_quote) {
                return 'À définir après diagnostic';
            }
            if (this.effectivePrice === null) {
                return 'Selon la longueur choisie';
            }

            return money(Math.max(this.effectivePrice - s.deposit_amount, 0));
        },

        get needsHairLength() {
            return !!this.selectedService?.has_length_pricing;
        },

        money,

        hairLengthLabel() {
            return HAIR_LENGTH_LABELS[this.hairLength] || '—';
        },

        hairColorLabel() {
            return HAIR_COLOR_LABELS[this.hairColor] || '—';
        },

        // === Grille calendrier =================================================================
        get calendarLeadingBlanks() {
            if (this.calendarDays.length === 0) {
                return 0;
            }
            const [y, m, d] = this.calendarDays[0].date.split('-').map(Number);
            const firstDay = new Date(y, m - 1, d);

            // 0 = lundi ... 6 = dimanche
            return (firstDay.getDay() + 6) % 7;
        },

        get calendarMonthLabel() {
            const [y, m] = this.calendarMonth.split('-').map(Number);
            const date = new Date(y, m - 1, 1);
            const label = new Intl.DateTimeFormat('fr-FR', { month: 'long', year: 'numeric' }).format(date);

            return label.charAt(0).toUpperCase() + label.slice(1);
        },

        get canGoPrevMonth() {
            if (!this.minDate) {
                return true;
            }
            const [my, mm] = this.calendarMonth.split('-').map(Number);
            const [ty, tm] = this.minDate.split('-').map(Number);

            return my > ty || (my === ty && mm > tm);
        },

        get canGoNextMonth() {
            if (!this.maxDate) {
                return true;
            }
            const [my, mm] = this.calendarMonth.split('-').map(Number);
            const [ty, tm] = this.maxDate.split('-').map(Number);

            return my < ty || (my === ty && mm < tm);
        },

        // === Actions =================================================================================
        async selectService(id) {
            const changed = this.selectedServiceId !== id;
            this.selectedServiceId = id;

            if (changed) {
                this.selectedDate = null;
                this.selectedSlot = null;
                this.slots = [];
                this.calendarDays = [];
                this.calendarMonth = currentMonthKey();
                await this.loadCalendar();
            }
        },

        async changeMonth(delta) {
            if (delta < 0 && !this.canGoPrevMonth) {
                return;
            }
            if (delta > 0 && !this.canGoNextMonth) {
                return;
            }

            const [y, m] = this.calendarMonth.split('-').map(Number);
            const d = new Date(y, m - 1 + delta, 1);
            this.calendarMonth = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
            await this.loadCalendar();
        },

        async loadCalendar() {
            if (!this.selectedService) {
                return;
            }

            this.calendarLoading = true;
            this.calendarError = null;

            try {
                const { data } = await window.axios.get('/reservation/availability/dates', {
                    params: { service: this.selectedService.slug, month: this.calendarMonth },
                });
                this.calendarDays = data.days;
                this.minDate = data.min_date;
                this.maxDate = data.max_date;
            } catch (e) {
                this.calendarError = 'Impossible de charger le calendrier pour le moment. Merci de réessayer.';
            } finally {
                this.calendarLoading = false;
            }
        },

        async pickDate(day) {
            if (!day.bookable) {
                return;
            }
            this.selectedDate = day.date;
            this.selectedSlot = null;
            await this.loadSlots();
        },

        formattedDate(dateStr) {
            if (!dateStr) {
                return '';
            }
            const [y, m, d] = dateStr.split('-').map(Number);
            const date = new Date(y, m - 1, d);
            const formatted = new Intl.DateTimeFormat('fr-FR', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            }).format(date);

            return formatted.charAt(0).toUpperCase() + formatted.slice(1);
        },

        dayNumber(dateStr) {
            return String(parseInt(dateStr.split('-')[2], 10));
        },

        async loadSlots() {
            if (!this.selectedService || !this.selectedDate) {
                return;
            }

            this.slotsLoading = true;
            this.slotsError = null;
            this.slots = [];

            try {
                const { data } = await window.axios.get('/reservation/availability/slots', {
                    params: { service: this.selectedService.slug, date: this.selectedDate },
                });
                this.slots = data.slots;
            } catch (e) {
                this.slotsError = 'Impossible de charger les créneaux pour le moment. Merci de réessayer.';
            } finally {
                this.slotsLoading = false;
            }
        },

        pickSlot(slot) {
            if (!slot.available) {
                return;
            }
            this.selectedSlot = slot;
        },

        selectHairLength(value) {
            this.hairLength = value;
        },

        selectHairColor(value) {
            this.hairColor = value;
            if (value !== 'autre') {
                this.hairColorDetails = '';
            }
        },

        // === Validation légère avant envoi ==========================================================
        validate() {
            this.errors = {};
            let valid = true;

            if (!this.selectedServiceId) {
                this.submitError = 'Merci de choisir une expérience de lissage.';
                valid = false;
            }
            if (!this.selectedDate || !this.selectedSlot) {
                this.submitError = this.submitError || 'Merci de choisir une date et un créneau.';
                valid = false;
            }
            if (this.needsHairLength && !this.hairLength) {
                this.errors.hair_length = 'Merci d’indiquer la longueur de vos cheveux : le tarif en dépend.';
                this.submitError = this.submitError || 'Merci d’indiquer la longueur de vos cheveux.';
                valid = false;
            }

            if (!this.profile.first_name || this.profile.first_name.trim().length < 2) {
                this.errors.first_name = 'Merci d’indiquer votre prénom.';
                valid = false;
            }
            if (!this.profile.last_name || this.profile.last_name.trim().length < 2) {
                this.errors.last_name = 'Merci d’indiquer votre nom.';
                valid = false;
            }

            const digits = (this.profile.phone || '').replace(/\D/g, '');
            if (digits.length < 6) {
                this.errors.phone = 'Merci d’indiquer un numéro de téléphone valide.';
                valid = false;
            }

            if (!this.profile.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.profile.email)) {
                this.errors.email = 'Merci d’indiquer une adresse e-mail valide.';
                valid = false;
            }

            return valid;
        },

        // === Envoi de la réservation =================================================================
        async submit() {
            if (this.submitting) {
                return;
            }

            this.submitError = null;

            if (!this.validate()) {
                return;
            }

            this.submitting = true;

            const notesParts = [];
            if (this.hairColor === 'autre' && this.hairColorDetails) {
                notesParts.push('Précisions cheveux : ' + this.hairColorDetails);
            }
            if (this.profile.message) {
                notesParts.push('Message : ' + this.profile.message);
            }

            const chemicalHistory = {
                naturelle: [],
                coloration: ['coloration'],
                'decoloration-balayage': ['decoloration-balayage'],
                decoloration: ['decoloration'],
                autre: ['autre'],
            }[this.hairColor] || [];

            try {
                const { data } = await window.axios.post('/reservation', {
                    lissage_service_id: this.selectedServiceId,
                    appointment_date: this.selectedDate,
                    start_time: this.selectedSlot.start,
                    first_name: this.profile.first_name,
                    last_name: this.profile.last_name,
                    phone: this.profile.phone,
                    email: this.profile.email,
                    hair_length: this.hairLength || null,
                    chemical_history: chemicalHistory,
                    hair_notes: notesParts.join('\n') || null,
                });

                window.location.href = data.redirect;
            } catch (error) {
                const response = error.response;

                if (response && response.status === 409) {
                    this.submitError = response.data.message;
                    // Le créneau vient d'être pris : on le désélectionne et on
                    // recharge la liste, sans rien perdre d'autre (prestation,
                    // date, cheveux, coordonnées restent renseignés).
                    this.selectedSlot = null;
                    await this.loadSlots();
                    this.$nextTick(() => {
                        document.getElementById('creneaux')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                } else if (response && response.status === 422) {
                    const fieldErrors = response.data.errors || {};
                    this.errors = Object.fromEntries(
                        Object.entries(fieldErrors).map(([key, messages]) => [key, messages[0]]),
                    );
                    this.submitError = 'Merci de vérifier les informations saisies.';
                } else {
                    this.submitError = 'Une erreur est survenue. Merci de réessayer dans quelques instants.';
                }
            } finally {
                this.submitting = false;
            }
        },
    }));
});
