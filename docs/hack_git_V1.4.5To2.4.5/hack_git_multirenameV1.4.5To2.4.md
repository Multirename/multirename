# gitmodulesSymlink .gitmodules is a symbolic link and github.com

Ein Repository ist bei github.com irgendwie kaputt und verweigert sogar einen
`push --force`.


## Vorwort

Situation: Ein Push nach +6 Jahren zu github.com funktionierte nicht mehr. Huch. 
Was ist passiert?

Ein lokales `git fsck --full` zeigte dann Probleme, die github.com bzw. `git`
selbst nicht mag. Habe ich beinahe noch nie gesehen oder nicht ernster beachtet,
da es eigentlich bei aktuellen Projekten behoben wurde und noch nie bei diesem
einen Repository gesehen. Lokal habe ich mehrere Klienten und ein lokales master
Repository/Remote bevor etwas öffentlich geht.

hmm. Und nun?

**Das Problem**: Symlinks zu `git` internen Dateien wie `.gitmodules` (selbst wenn
sie intern im Repository bzw. Branch vorhanden sind) sollen nicht mehr verwendet
werden. Grr.

Ja, allgemeine Erklärungen habe ich verstanden. Passen aber nicht in meinem Fall
und Denk- bzw. Arbeitsweise:

Meine `.gitmodules` waren immer eine passende Datei im gleichen Verzeichnis, um
bei einem Merge darauf aufmerksam zu werden, das sich etwas ändert.
Branches sind bei mir u.a Staging- Bereiche) 
```
    .gitmodules -> symlinks zu einer dieser Dateien 
                    (abhängig zum aktuellen Branch)

        -> .gitmodules_stable
                ^ ------------------ Staging to stable, release
        ->  .gitmodules_testing
                ^ ------------------ Staging to testing, testing and preview
        ->  .gitmodules_unstable
                ^ ^ ^---<---------<-- Feature branch A
                | +-----<---------<-- Feature branch B
                +-------<---------<-- Feature branch C ...
```

Bei einem merge unstable -> testing (-ff) würden Einstellungen von unstable
einfach in testing mit einbezogen/übernommen werden, ohne darauf aufmerksam zu
werden. Was auch zur Folge hat, dass man nicht mehr darauf aufmerksam gemacht
wird, das die Abhängigkeiten der Submodule ebenfalls angehoben werden müssen,
bevor der merge sauber/ fertig ist.

Wenn an mehreren Submodulen gearbeitet wird, macht das durchaus Sinn. Gerade
dann, wenn man eben nicht alles online hat oder haben will, um einen aktuellen
Stand zu prüfen/testen etc. bevor man dann die Hashes der Submodule bindet und
alles dann je nach Abhängigkeit Stück für Stück öffentlich stellt.
Was für ein Aufwand :-/

Mit `git` selbst (Version 2.39.0, Debian Bookworm, 2023) kann man lokal noch
weiter arbeiten. Bei github.com ist allerdings Schluss mit lustig. Wenn es im
Repository `git` System Dateien gibt wie `.gitignore` oder `.gitmodules` die
Symlinks sind, verweigert github.com jegliche Arbeit, die Bugs beseitigen zu
können. Weder über das Webinterface noch via `git push --force` selbst. Uff.

Also: Nach 6 Jahren! Dazwischen gab es lokal immer wieder mal Updates, die aber
nicht öffentlich gestellt wurden. Das Betriebssystem, ein Debian, wird etwa alle
zwei Jahre aktualisiert. Neue `git` Versionen sind also mind. 3 Stk. seither für
dieses Projekt dabei. Mit der aktuellen Version von `git` kann ich die Probleme
allerdings nicht beheben. Und das ist wirklich schlecht!

Die problembehafteten Commits konnte ich nicht für einen `rebase` 'auschecken'/
verwenden da `git` mir sagt: "Du solltst keine symlinks benutzen. Abbruch."

    ".git/rebase-merge/git-rebase-todo" 197L, 7523B geschrieben
    Fehler: Ungültiger Pfad '.gitmodules'
    Fehler: konnte HEAD nicht loslösen

Ja, witzig!

Und nun?

Das Netz ist bei einer Suche beinahe leer hierzu oder ich habe an der falschen
Stelle gesucht?

Muss ein Repository immer Top-aktuell zur `git` Version sein, damit `git` seinen
dienst tut?

Wie viele gute Anwendungen gibt es, die nicht mehr aktualisiert werden, weil sie
gut laufen und nicht aktualisiert werden müssen?

Was ist wenn nach einigen Jahren das Projekt Verwaltungs- Tool abbricht, weil
man zwischenzeitlich nichts machen musste?

Mein Anspruch: Der Wahnsinn von Applikationen mit einer Lebenszeit von 1, 2
Jahren darf auf keinen Fall für `git` selbst und/oder, dessen Dienste/ Provider
gelten.

Muss ich nun alle meine Repos inkl. des zugrunde liegenden Betriessystem/ OS 
einfrieren/ im Back-up haben, um nach einiger/ längerer Zeit etwas gangbar zu 
haben, was wieder öffentlich gehen kann (falls wieder einmal etwas nicht geht)?

github.com hat es bewiesen: Durch das Verweigern von Funktionen konnte man keine
Fehler mehr beheben. Ich musste die komplette Historie des Repositories
erneuern, was natürlich extreme Auswirkungen auf alle Forks oder Klone hat.

Das kann nicht im Sinne der Erfindung sein!



## Technisches Set-up in kurz

- Vor etwa 6 Jahren mit einem aktuellen Debian Betriebssystem/ `git` Version den
  letzten Push zu github.com gemacht.

- Lokal (alle Klienten) min. alle 2. Jahre gab es neue Debian Major OS Updates.
  Lokale Workstations: `git` Version 2.39.2

- Lokal gibt es einen Server, der als Primary Master Server für `git` Projekte 
  existiert und dessen `git` Version sehr selten aktualisiert wird.
  Lokal Master: `git` Version 2.33.0

- Lokal wurde über die Klienten immer wieder mal ein Push zum Master Server und
  dieses Projekt gemacht. Funktionierte ohne Auffälligkeiten seit Anbeginn und
  immer noch.

Und nun sind github.com aber auch `git` selbst im Detail zickig.

github.com, da es einen Push nicht mehr akzeptiert:
    
    index-pack failed
    remote: error: object [hash]: gitmodulesSymlink: .gitmodules is a symbolic link

Und `git` selbst bricht auch ab. Ich kann die Probleme so also nicht lösen.

    git filter-branch --tree-filter 'rm -f .gitmodules' HEAD
    Rewrite [SomeHash] (3/185) (0 seconds passed, remaining 0 predicted) \
        error: Invalid path '.gitmodules' Could not initialize the index

    Titel : Removing a File from Every Commit
    Quelle: https://git-scm.com/book/en/v2/Git-Tools-Rewriting-History

Der gleiche Versuch scheiterte ebenfalls mit einer älteren VM, wo noch `git`
Version 2.11.0 verfügbar war. hmm.



# Hacking

Wo fängt man an zu suchen, wenn nicht einmal bei stackoverflow.com ideen zu
Möglichkeiten folgen?

Die `git` History neu zu schreiben ist schlimm!

Es macht zig Dinge kaputt (Tags, Submodul Bindungen). Die Anzahl der Commits 
ist mit 185 überschaubar und vergleichsweise klein, aber es hat riesige
Auswirkungen auf alles, was man zuvor einmal gemacht hat und was man manuell
nacharbeiten muss. Jede Version (Tag) und alle Abhängigkeiten der Submodule
müssen wieder hergestellt werden, wenn man Services wie github.com (und
vermutlich auch andere) weiterhin nutzen möchte. Auch eigene 'Provider' wie eine
lokale `gitea` oder `gitlab` Instanz betrifft es!



## Die Untersuchung

Ich wusste nicht einmal, das es so etwas wie `git fsck` gibt. `git fsck --full`
zeigt noch mehr Fehler, die sich ggf. im Laufe der Zeit bei einem `git`
Repository einschleichen (Vieles davon ist in der Regel einfacher Natur).

    `git fsck`

Und da zeigten sie sich dann, die Probleme.

Ideen:

1.  git-repair:
    Was auch immer das `git-repair` Projekt bei meinem Repository machte: Es hat 
    die History neu geschrieben, nicht aber die Fehler bereinigt, die ich mir
    erhofft hatte. Unbrauchbar, da es zwar irgendetwas an weiteren Fehlern fand,
    Nicht aber jene Fehler die essentiell sind um weiter zu kommen.

2.  Jeden Commit auschecken und schauen, welcher Probleme macht.
    Ggf. fix via rebase bis alle Probleme weg sind.
    Diese Handarbeit hat Stunden über Stunden gedauert und war schlimmer als
    die eigentliche Implementierung dessen, was Verwaltet werden soll. Abbruch.

3.  Da dieses Verbot der Nutzung von Symlinks für `git` Dateien mir relativ neu
    Schien, blieb mir also nur noch der weg nach hinten. Ältere Linux
    Betriebssysteme kann man ja Gott sei Dank immer noch irgendwie bekommen (um
    einen möglichen `git` Kandidat zu erwischen der dann hoffentlich 
    funktioniert), wenn man sie nicht noch zufällig als DVD Image wo rumliegen
    hat (Auch das kann manchmal essenziell werden und man ist immer noch gut
    beraten, so eine Variante als Option zu haben wenn man in die Vergangenheit
    Reisen muss!).
 

## Lösungsweg

Option 3. war dann die Lösung. Ein Debian Wheezy (7.3) brachte eine `git` 
Version 1.7.10.4 mit, mit der ich einen Rebase auf einen der mir bekannten und 
problembehafteten Commits zulies. Ich bin vielleicht zu weit in die
Vergangenheit gereist, aber hiermit hatte ich erst einmal eine Basis zum testen.
Vielleicht bleibt es so und alles ist wieder gut!?

Darauf folgend und, um wieder automatisch alles zu erledigen, kam mir der
git filter-branch wieder in den Gedanken:

    git filter-branch --tree-filter 'rm -f .gitmodules' HEAD

Tat seinen Dienst, half aber nichts Richtung github.com als `push --force` für
diesen Branch.

    - git filter-branch --tree-filter 'rm -f .gitmodules' HEAD
    + git filter-branch --tree-filter 'rm -f .gitmodules'

Also: komplett und über alles bitte.

Damit diese Situation aber ordentlich wird:

    .gitmodules ->- symlink zum einem dieser... ->-
    -> .gitmodules_master
    -> .gitmodules_stable
    -> .gitmodules_testing
    -> .gitmodules_unstable

'rm -f .gitmodules' löscht, löst aber nicht die zugrunde liegenden Bindungen der 
Submodule. Auch hier gab es Mischungen in der Nutzung/ Verwendung, wie die
Historie beim genauen betrachten deutlich zeigte. Aber der Fahrplan, es so zu 
machen, blieb einheitlich.

Ich habe das lokale Repository recht oft aus dem Back-up holen müssen, um dann
die vielen Steps von Tests wiederholen zu können...

Schließlich: Submodule (`.gitmodules`) waren in der Idee immer ein Symlink.
Damit es zukünftig für die Reparatur passt, müssen also alle symlinks in eine
echte Datei aufgelöst werden, damit der rebase via `git filter-branch
--tree-filter` sauber laufen kann und die Abhängigkeiten in der Basis sauber
bleiben. Und das sieht dann wie folgt aus:

    # Wenn .gitmodules ein symlink ist: 
    #   - Finde den Pfad zur echten Datei
    #   - Lösche den Symlink
    #   - Kopiere die echte Datei zu .gitmodules
    git filter-branch --tree-filter \
        'if [ -h .gitmodules ];then loc=$(readlink .gitmodules); rm -f .gitmodules; cp $loc .gitmodules; fi;'
    
Damit war dieses Problem erledigt. Symlinks weg und durch den echten Inhalt
getauscht. Allerdings wurde nun die komplette History neu geschrieben. Lokal als
auch die neueren `git` Versionen (Klienten und lokaler Server) meldeten
keinerlei Fehler mehr.

Für github.com galt nun folgendes:

    git push --force [--tags]

für alle branches! Sonnst kommt der Salat durch irgend ein anderen Fehler dort
vielleicht zurück und man muss von vorn beginnen.

Im Anschluss, oder lokal vorweg: Die alte History nach git Tags untersuchen und
die Tags in der neuen History setzen.

    git tag -a [VERSION] [neuer commit hash]
    git push --tags

Und natürlich nun die ganzen Metadaten z.b. zu einem Release auf den Reporsitory
Web-Seiten von github.com müssen aktualisiert werden.


# Fazit

Ich mag diese Funktionsweise von `git` aktuell hierzu nicht. Symlinks helfen in
meinem Fall. Sie sind transparent und in jedem Branch innerhalb des Repositories
verfügbar und werden gepflegt. 

Das wichtigste: Sie erleichten die Arbeit da sie auf Veränderungen aufmerksam
machen. Das spart ungemein viel Zeit und das hinterher laufen wenn man vergisst
die Submodule zu korrigieren.

