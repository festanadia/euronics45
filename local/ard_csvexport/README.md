# Local CSV Export Plugin

Plugin Moodle per l'estrazione automatica di dati in formato CSV e trasferimento tramite SFTP.

## Caratteristiche

- **Task schedulato automatico**: Esegue l'estrazione il primo giorno di ogni mese alle 3:00 UTC
- **Estrazione dati personalizzata**: Query SQL configurabile per estrarre i dati di completamento corsi
- **Trasferimento SFTP sicuro**: Upload automatico del file CSV su server remoto
- **Configurazione amministratore**: Interfaccia web per configurare SFTP e query
- **Test connessione**: Verifica la connessione SFTP direttamente dall'interfaccia
- **Logging completo**: Tracciamento dettagliato delle operazioni per debugging

## Installazione

1. **Carica il plugin**:
   ```bash
   cd /path/to/moodle
   cp -r local_ard_csvexport local/ard_csvexport
   ```

2. **Installa tramite interfaccia web**:
   - Vai su `Site Administration > Notifications`
   - Segui il processo di installazione

3. **Configura le impostazioni**:
   - Vai su `Site Administration > Plugins > Local plugins > CSV Export`
   - Configura le credenziali SFTP
   - Testa la connessione
   - Abilita il plugin

## Configurazione SFTP

### Impostazioni richieste:
- **SFTP Host**: Hostname o IP del server SFTP
- **SFTP Port**: Porta (default: 22)
- **Username**: Nome utente per l'autenticazione
- **Password**: Password per l'autenticazione
- **Remote Path**: Percorso di destinazione sul server (default: /)

### Test della connessione:
Utilizza il pulsante "Test SFTP Connection" nelle impostazioni per verificare la configurazione.

## Pianificazione Task

Il task è configurato per eseguirsi:
- **Giorno**: 1° di ogni mese
- **Ora**: 3:00 AM UTC
- **Frequenza**: Mensile

### Monitoraggio:
- Vai su `Site Administration > Server > Tasks > Scheduled tasks`
- Cerca "Monthly CSV Export via SFTP"
- Verifica il "Next run" e lo stato

## Formato Dati Export

Il plugin genera **due file CSV** per il mese precedente:

### 1. File Completamenti Corsi
**Nome**: `caldic_course_completions_YYYY-MM_timestamp.csv`

Contiene i completamenti corsi del mese precedente con i seguenti campi:

| Campo | Descrizione |
|-------|-------------|
| CPNT_ID | ID componente (CPNT_MERC_{course_id}) |
| CPNT_TYP_ID | Tipo componente (ONLINE) |
| REV_DTE | Data di revisione formattata |
| DMN_ID | Dominio (BU_EUR) |
| NOTACTIVE | Stato attivo corso (Y/N) |
| CMPL_STAT_ID | Stato completamento (COURSE_COMPLETE) |
| CPNT_TITLE | Titolo corso (shortname) |

### 2. File Dettagli Corsi
**Nome**: `caldic_course_details_YYYY-MM_timestamp.csv`

Contiene i dettagli dei corsi che hanno avuto completamenti nel mese precedente con gli stessi campi del primo file ma organizzati per corso (non per completamento).

## Filtri Applicati

- **Utenti**: Solo istituzione 'caldic', escluso 'review_caldic'
- **Corsi**: Solo Learning Path (course_type = 2) o SCORM (course_type = 1 AND mono_course_type = 4)
- **Periodo**: Completamenti del mese precedente all'esecuzione
- **Stato**: Solo completamenti con `timecompleted` non nullo

## Requisiti Tecnici

### Estensioni PHP richieste (una delle due):
1. **phpseclib** (raccomandato): Installabile via Composer
   ```bash
   composer require phpseclib/phpseclib
   ```

2. **SSH2 Extension**: Installazione via PECL/package manager
   ```bash
   # Ubuntu/Debian
   sudo apt-get install php-ssh2
   
   # CentOS/RHEL
   sudo yum install php-ssh2
   ```

### Permessi Moodle:
- L'utente che esegue il cron deve avere accesso in scrittura a `$CFG->dataroot/temp/ard_csvexport`
- Il plugin crea automaticamente la directory temporanea con permessi 755
- Configurazione del cron per esecuzione ogni minuto (raccomandato)
- **Load Balancing**: Utilizza moodledata condivisa per gestire file temporanei in ambienti bilanciati

### Gestione File Temporanei:
- I file temporanei sono creati in `$CFG->dataroot/temp/ard_csvexport/`
- Pulizia automatica dei file più vecchi di 24 ore
- Protezione `.htaccess` per prevenire accesso web diretto
- Compatibilità con server load-balanced (moodledata condivisa)

## File Generati

Il sistema genera due file CSV ogni mese:

### 1. File Completamenti
```
caldic_course_completions_YYYY-MM_YYYY-MM-DD_HH-mm-ss.csv
```
Esempio: `caldic_course_completions_2025-05_2025-06-01_03-00-15.csv`

### 2. File Dettagli Corsi  
```
caldic_course_details_YYYY-MM_YYYY-MM-DD_HH-mm-ss.csv
```
Esempio: `caldic_course_details_2025-05_2025-06-01_03-00-16.csv`

**Nota**: Entrambi i file vengono caricati nella stessa directory SFTP configurata.

## Troubleshooting

### Task non si esegue:
1. Verifica che il cron di Moodle sia attivo
2. Controlla i log in `Site Administration > Server > Tasks > Scheduled tasks`
3. Verifica che il plugin sia abilitato nelle impostazioni

### Errori SFTP:
1. Testa la connessione dalle impostazioni
2. Verifica credenziali e permessi sul server SFTP
3. Controlla i log del task per dettagli specifici

### Problemi con file temporanei:
1. Verifica permessi di scrittura su `$CFG->dataroot/temp/ard_csvexport/`
2. In ambienti load-balanced, assicurati che moodledata sia condivisa tra server
3. Controlla spazio disco disponibile su moodledata
4. La pulizia automatica rimuove file più vecchi di 24 ore

## Log e Monitoring

I log del task sono visibili in:
- **Via CLI**: `php admin/cli/scheduled_task.php --execute=\\local_ard_csvexport\\task\\monthly_csv_export`
- **Via Web**: Logs del task schedulato in Site Administration
- **File di log**: Standard Moodle error logs

## Supporto

Per problemi o personalizzazioni:
1. Verifica la configurazione seguendo questa guida
2. Controlla i log per messaggi di errore specifici
3. Testa manualmente la connessione SFTP
4. Verifica che la query SQL funzioni correttamente

## Licenza

Questo plugin è rilasciato sotto licenza GPL v3 o successiva.
