document.addEventListener('alpine:init', () => {
    Alpine.data('inventuraLampLogin', (initialOn = false) => ({
        isOn: initialOn,
        showPassword: false,
        audioCtx: null,

        // Koordinat pangkal & ujung default tali (dalam viewBox SVG 280x500)
        anchorX: 162,
        anchorY: 138,
        restX: 162,
        restY: 226,

        // State fisika spring tali
        curX: 162,
        curY: 226,
        velX: 0,
        velY: 0,
        dragging: false,
        activePointerId: null,
        startClientX: 0,
        startClientY: 0,
        movedDist: 0,
        rafId: null,

        // Handler global agar drag tidak pernah lepas di layar sentuh HP
        _boundMove: null,
        _boundUp: null,
        _boundTouchGuard: null,

        init() {
            this.renderCord();
            this.spawnFireflies();

            this._boundMove = (e) => this.onCordMove(e);
            this._boundUp = (e) => this.onCordUp(e);
            this._boundTouchGuard = (e) => {
                if (this.dragging) e.preventDefault();
            };
        },

        destroy() {
            if (this.rafId) cancelAnimationFrame(this.rafId);
            this.unbindWindowDrag();
        },

        bindWindowDrag() {
            window.addEventListener('pointermove', this._boundMove, { passive: false });
            window.addEventListener('pointerup', this._boundUp);
            window.addEventListener('pointercancel', this._boundUp);
            window.addEventListener('touchmove', this._boundTouchGuard, { passive: false });
        },

        unbindWindowDrag() {
            window.removeEventListener('pointermove', this._boundMove);
            window.removeEventListener('pointerup', this._boundUp);
            window.removeEventListener('pointercancel', this._boundUp);
            window.removeEventListener('touchmove', this._boundTouchGuard);
        },

        playSwitchClick(turningOn) {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!this.audioCtx) this.audioCtx = new AudioCtx();
                if (this.audioCtx.state === 'suspended') this.audioCtx.resume();

                const now = this.audioCtx.currentTime;
                const osc = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();

                osc.type = 'triangle';
                osc.frequency.setValueAtTime(turningOn ? 880 : 520, now);
                osc.frequency.exponentialRampToValueAtTime(turningOn ? 240 : 160, now + 0.045);

                gain.gain.setValueAtTime(0.14, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.055);

                osc.connect(gain);
                gain.connect(this.audioCtx.destination);
                osc.start(now);
                osc.stop(now + 0.06);
            } catch (_) {
                // Abaikan jika browser memblokir audio
            }
        },

        toggleLamp() {
            this.isOn = !this.isOn;
            this.playSwitchClick(this.isOn);
        },

        triggerQuickPull() {
            this.curY = this.restY + 42;
            this.velY = 4;
            this.renderCord();
            this.toggleLamp();
            this.startSpringBack();
        },

        onCordDown(e) {
            if (e.pointerType === 'mouse' && e.button !== 0) return;
            e.preventDefault();

            if (this.rafId) cancelAnimationFrame(this.rafId);

            this.dragging = true;
            this.activePointerId = e.pointerId;
            this.startClientX = e.clientX;
            this.startClientY = e.clientY;
            this.movedDist = 0;

            this.bindWindowDrag();
        },

        onCordMove(e) {
            if (!this.dragging) return;
            if (this.activePointerId !== null && e.pointerId !== this.activePointerId) return;
            e.preventDefault();

            const svg = this.$refs.lampSvg;
            if (!svg) return;

            const rect = svg.getBoundingClientRect();
            const scaleX = 280 / rect.width;
            const scaleY = 500 / rect.height;

            const dx = (e.clientX - this.startClientX) * scaleX;
            const dy = (e.clientY - this.startClientY) * scaleY;
            this.movedDist = Math.hypot(dx, dy);

            const clampedX = Math.max(-44, Math.min(44, dx * 0.7));
            const clampedY = Math.max(-10, Math.min(78, dy * 0.9));

            this.curX = this.restX + clampedX;
            this.curY = this.restY + clampedY;
            this.renderCord();
        },

        onCordUp(e) {
            if (!this.dragging) return;
            if (this.activePointerId !== null && e.pointerId !== this.activePointerId) return;

            this.dragging = false;
            this.activePointerId = null;
            this.unbindWindowDrag();

            const pullDepth = this.curY - this.restY;

            if (pullDepth >= 22) {
                this.toggleLamp();
                this.startSpringBack();
            } else if (this.movedDist < 8) {
                this.triggerQuickPull();
            } else {
                this.startSpringBack();
            }
        },

        startSpringBack() {
            if (this.rafId) cancelAnimationFrame(this.rafId);

            const stiffness = 0.16;
            const damping = 0.74;

            const step = () => {
                const forceX = (this.restX - this.curX) * stiffness;
                const forceY = (this.restY - this.curY) * stiffness;

                this.velX = (this.velX + forceX) * damping;
                this.velY = (this.velY + forceY) * damping;

                this.curX += this.velX;
                this.curY += this.velY;

                this.renderCord();

                const stillMoving =
                    Math.abs(this.restX - this.curX) > 0.25 ||
                    Math.abs(this.restY - this.curY) > 0.25 ||
                    Math.abs(this.velX) > 0.25 ||
                    Math.abs(this.velY) > 0.25;

                if (stillMoving) {
                    this.rafId = requestAnimationFrame(step);
                } else {
                    this.curX = this.restX;
                    this.curY = this.restY;
                    this.velX = 0;
                    this.velY = 0;
                    this.renderCord();
                }
            };

            this.rafId = requestAnimationFrame(step);
        },

        renderCord() {
            const path = this.$refs.cordPath;
            const hitPath = this.$refs.cordHitPath;
            const handle = this.$refs.cordHandle;
            if (!path || !handle) return;

            const midX = (this.anchorX + this.curX) / 2 + (this.curX - this.anchorX) * 0.18;
            const midY = (this.anchorY + this.curY) / 2;
            const d = `M ${this.anchorX} ${this.anchorY} Q ${midX.toFixed(1)} ${midY.toFixed(1)} ${this.curX.toFixed(1)} ${this.curY.toFixed(1)}`;

            path.setAttribute('d', d);
            if (hitPath) hitPath.setAttribute('d', d);
            handle.setAttribute('transform', `translate(${this.curX.toFixed(1)}, ${this.curY.toFixed(1)})`);
        },

        spawnFireflies() {
            const container = this.$refs.fireflies;
            if (!container || container.childElementCount > 0) return;

            for (let i = 0; i < 18; i++) {
                const dot = document.createElement('span');
                const size = (Math.random() * 4 + 2.5).toFixed(1);
                dot.className = 'lamp-firefly';
                dot.style.width = `${size}px`;
                dot.style.height = `${size}px`;
                dot.style.left = `${(Math.random() * 92 + 4).toFixed(1)}%`;
                dot.style.top = `${(Math.random() * 88 + 6).toFixed(1)}%`;
                dot.style.setProperty('--dur', `${(Math.random() * 5 + 5).toFixed(1)}s`);
                dot.style.setProperty('--delay', `${(Math.random() * 4).toFixed(1)}s`);
                dot.style.setProperty('--tx', `${(Math.random() * 34 - 17).toFixed(0)}px`);
                dot.style.setProperty('--ty', `${-(Math.random() * 38 + 16).toFixed(0)}px`);
                dot.style.setProperty('--max-op', `${(Math.random() * 0.5 + 0.25).toFixed(2)}`);
                container.appendChild(dot);
            }
        }
    }));
});