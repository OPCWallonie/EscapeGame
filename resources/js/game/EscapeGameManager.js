// resources/js/game/EscapeGameManager.js
class EscapeGameManager {
    constructor() {
        this.gameId = null;
        this.currentRoom = null;
        this.collectedDigits = [];
        this.teamMembers = new Map();
        this.arManager = null;
    }

    async initializeGame(gameId) {
        this.gameId = gameId;
        
        // Initialiser les canaux WebSocket
        this.setupWebSocketChannels();
        
        // Initialiser la réalité augmentée
        if (this.hasARSupport()) {
            this.arManager = new ARManager();
        }
        
        // Charger l'état actuel du jeu
        await this.loadGameState();
    }

    setupWebSocketChannels() {
        // Canal principal du jeu
        this.gameChannel = window.Echo.channel(`game.${this.gameId}`);
        
        // Événements globaux du jeu
        this.gameChannel
            .listen('.room.unlocked', (e) => {
                this.onRoomUnlocked(e);
            })
            .listen('.digit.found', (e) => {
                this.onDigitFound(e);
            })
            .listen('.team.progress', (e) => {
                this.updateTeamProgress(e);
            });
    }

    // SALLE 1: Galerie commerçante - Clé AR
    async initGalleryChallenge() {
        const arScene = document.getElementById('ar-scene');
        
        // Initialiser la détection AR pour les portes
        this.arManager.startTracking({
            targets: ['door-1', 'door-2', 'door-3', 'door-4'],
            onTargetFound: (target) => {
                this.showARKey(target);
            }
        });

        // Synchroniser la découverte de la clé
        this.roomChannel.listen('.key.found', (e) => {
            this.highlightCorrectDoor(e.doorId);
        });
    }

    // SALLE 2: Salon de coiffure virtuel
    async initHairdresserChallenge() {
        // Créer l'environnement virtuel
        const virtualSalon = new VirtualSalon();
        
        // Mini-jeu de coiffure synchronisé
        virtualSalon.onStyleComplete = (style) => {
            axios.post(`/api/game/${this.gameId}/room/hairdresser/complete`, {
                style_data: style
            });
        };

        // Synchroniser les actions des autres joueurs
        this.roomChannel.listen('.style.updated', (e) => {
            virtualSalon.updateOtherPlayerStyle(e.playerId, e.style);
        });
    }

    // SALLE 3-4: Navigation dans les caves
    async initNavigationChallenge() {
        const pathTracker = new PathTracker();
        
        // Tracker le parcours avec QR codes
        pathTracker.onQRScanned = (qrData) => {
            // Vérifier si c'est le bon chemin
            axios.post(`/api/game/${this.gameId}/room/navigation/checkpoint`, {
                qr_code: qrData,
                timestamp: Date.now()
            });
        };

        // Afficher la progression de l'équipe
        this.roomChannel.listen('.path.progress', (e) => {
            pathTracker.updateTeamPath(e.checkpoints);
        });
    }

    // SALLE 5-6: Challenges temporels
    async initTimedChallenges(roomType) {
        const timer = new SynchronizedTimer();
        
        // Synchroniser le timer entre tous les joueurs
        this.roomChannel.listen('.timer.update', (e) => {
            timer.syncWith(e.serverTime, e.remaining);
        });

        if (roomType === 'old-bar') {
            // Mini-jeu de préparation de cocktails
            const barGame = new CocktailMixer();
            barGame.onRecipeComplete = (recipe) => {
                this.submitChallenge('bar', recipe);
            };
        }
    }

    // SALLE 7: Centre de contrôle
    async initControlCenterChallenge() {
        // Vue sur la galerie - interaction AR
        const windowView = new InteractiveWindow();
        
        // Les joueurs doivent coordonner des actions
        windowView.onActionPerformed = (action) => {
            this.broadcastAction('control-center', action);
        };

        // Puzzle collaboratif
        this.roomChannel.listen('.puzzle.piece.placed', (e) => {
            windowView.updatePuzzle(e.pieceId, e.position);
        });
    }

    // SALLE 11: Final sur le toit
    async initRooftopFinale() {
        if (!this.arManager) {
            console.error('AR requis pour le final!');
            return;
        }

        // Détecter l'hélicoptère en AR
        this.arManager.loadModel('helicopter', {
            onLoad: () => {
                // Animation d'arrivée de l'hélico
                this.playHelicopterArrival();
            }
        });

        // Synchroniser l'apparition pour tous
        this.roomChannel.listen('.helicopter.spawned', (e) => {
            this.arManager.spawnAt(e.position, e.rotation);
        });
    }

    // Gestion des indices et chiffres du code
    onDigitFound(data) {
        this.collectedDigits.push({
            digit: data.digit,
            position: data.position,
            foundBy: data.player
        });

        // Mettre à jour l'interface
        this.updateCodeDisplay();
        
        // Notification
        this.showNotification(
            `${data.player.name} a trouvé le chiffre ${data.position + 1} !`
        );
    }

    // Système de hints progressifs
    requestHint() {
        axios.post(`/api/game/${this.gameId}/hint/request`, {
            room_id: this.currentRoom,
            progress: this.getCurrentProgress()
        }).then(response => {
            if (response.data.hint) {
                this.displayHint(response.data.hint);
            }
        });
    }

    // Sauvegarde automatique de la progression
    autoSaveProgress() {
        setInterval(() => {
            const progress = {
                room_id: this.currentRoom,
                collected_digits: this.collectedDigits,
                completed_challenges: this.completedChallenges,
                timestamp: Date.now()
            };

            axios.post(`/api/game/${this.gameId}/progress/save`, progress);
        }, 30000); // Toutes les 30 secondes
    }

    // Gestion des déconnexions/reconnexions
    handleReconnection() {
        window.Echo.connector.socket.on('reconnect', () => {
            // Recharger l'état du jeu
            this.loadGameState();
            
            // Rejoindre les canaux
            if (this.currentRoom) {
                this.joinRoom(this.currentRoom);
            }
        });
    }
}

// Classe pour gérer la réalité augmentée
class ARManager {
    constructor() {
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.arSession = null;
    }

    async initialize() {
        // Vérifier le support WebXR
        if ('xr' in navigator) {
            this.arSession = await navigator.xr.requestSession('immersive-ar', {
                requiredFeatures: ['hit-test', 'dom-overlay'],
                domOverlay: { root: document.getElementById('ar-overlay') }
            });
        }
    }

    startTracking(config) {
        // Implémenter le tracking AR
        // Utiliser AR.js ou 8th Wall pour la détection d'images
    }

    showARKey(target) {
        // Afficher une clé virtuelle en AR
        const keyModel = this.loadModel('golden-key');
        keyModel.position.copy(target.position);
        this.scene.add(keyModel);
    }
}

// Mini-jeu exemple : Mélangeur de cocktails
class CocktailMixer {
    constructor() {
        this.ingredients = [];
        this.targetRecipe = null;
        this.score = 0;
    }

    startGame(difficulty) {
        this.targetRecipe = this.generateRecipe(difficulty);
        this.setupUI();
        this.startTimer();
    }

    addIngredient(ingredient) {
        this.ingredients.push(ingredient);
        
        // Vérifier en temps réel
        if (this.checkRecipe()) {
            this.onRecipeComplete?.(this.ingredients);
        }
    }

    syncWithTeam(teamIngredients) {
        // Afficher ce que font les coéquipiers
        this.updateTeamDisplay(teamIngredients);
    }
}