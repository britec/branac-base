<?php
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 

$filter = new CTextFilter(); 

// Do it and store it all in variables in the Branax container.
$branax['title'] = "Redovisning";

$branax['main'] = <<<EOD
<article class="readable">
<h1>Redovisning av kursmomenten</h1>

<h2>Kmom01: Kom igång med Objektorienterad PHP</h2>

<p>Med nyligen avslutad htmlphp var labmiljön redan på plats, således inga problem med detta, jag kör Windows Vista och Cygwin. Artikeln “20 steg för att komma igång med PHP”, som jag tycker är en är en mycket trevlig tutorial, var ju också redan avklarad sedan tidigare. Artikeln om Anax gav en positiv bild av ramverkets struktur. Dock ganska omfattande innehåll vid en första anblick och jag bestämde mig för att inte göra några signifikanta ändringar vid portning till mitt eget "Branax". Relativt smärtfritt fick jag därför de bifogade testsidorna snart att snurra lika bra i Branax som i Anax. Jag skapade också ett git repository för koden och pushar upp en första release av <a href="https://github.com/britec/branac-base">branax-base</a> på GitHub.</p>

<p>Även vid konstruktion av Me-siten beslöt jag att lägga mig så nära förebilden som möjligt (tillräckligt mycket som kan ju gå fel i alla fall). Jag placerade en branax-klon i kmom01-katalogen och städar ur testsidor mm från webroot. Jag anpassade config-filen för siten och skapar en sid-controller för me-sidan, vars utseende blir helt enligt förebilden men utan slideshow och givetvis med ny text och andra bilder. Navbar adderades med copy/paste till respektive platser i config, och theme. Redovisningssidan clonades från exemplet utan problem. Heller inga problem att hämta hem modulen för källkodsvisning och lägga in den i Branax. Slutligen trivialt att lägga dumpfunktionen till bootstrappen.</p>

<p>Trevlig och lärorik övning. Inga egna utsvävningar i mitt fall utan mestadels följa-john (eller kanske Mikael). Utmanande men inte onödigt komplicerat.</p>

<p><a href="http://www.student.bth.se/~bjri15/dbwebb-kurser/oophp/me/kmom01/webroot/redovisning.php">Me-sida med denna redovisning på studentservern</a></p>


<h2>Kmom02: Objektorienterad programmering i PHP</h2>

<p>För mig blev oophp20-guiden, med lite om det mesta, en mycket trevlig liten uppfriskare i objektorienterad programmering i allmänhet och inte bara en php-övning. Jag har i oo-väg sedan tidigare en liten web-kurs i java och lite nödtorftig navigering i C++ kod vid behov. Inget kändes helt främmande men som vanligt snurrade det till med alla begreppen mot slutet. Jag läste ganska noga igenom det mesta av exemplkoden för att förstå den i detalj medan jag provkörde, men jag kodade inte så mycket själv.</p>

<p>Jag valde tärningsspelet och dess extraövning för fler spelare och spel mot datorn som uppgift. Det kändes från början att en hel del skulle kunna återanvändas från oophp-20 övningen. Jag använder mig således av dess CDice klass (orörd) för tärningen och CDiceHand (med en ny funktion för att producera tärningsbilder) för att hantera en runda. Ett objekt av klassen C100 utför sedan all spellogik och producerar all information om spelstatus för utskrift. Så småningom lade jag också till en klass CPlayer vars enskilda objekt håller information om en spelare (hade nog gått lika bra med en array). Slutligen ett objekt av klassen C100Wrapper som hanterar sessionsvariabeln och som vid behov initierar nytt spelobjektet med valt antal spelare (valmöjligheterna kan enkelt utökas). Sid-kontrollern är minimalistisk och anropar endast wrapperns run funktion.</p>

<p>En enkel prototyp var tidigt uppe och snurrade. Jag kände redan då att inslaget av objektorientering i arbetet möjligen blev lite begränsad. Nästan all ny funktionalitet utförs ju av en enda function i ett enda spelobjekt. I backspegeln gick nog 80% av arbetet till putsade på denna funktion, för att växla spelare och för automatkörning, för att  producerar relevant information och för att presentera denna någorlunda logiskt och begripligt (vilket jag av någon anledning hade särskilt svårt att bestämma mig för hur det skulle göras). Jag ville i detta läge inte lägga tid på avancerad layout eller styling av spel-sidan.</p>

<p>För automatkörningen valde jag en enkel (men någorlunda statistiskt motiverad) strategi för datorn att spela efter, den kastar på och sparar först om den når upp till 20 poäng i rundan.</p>

<p><a href="http://www.student.bth.se/~bjri15/dbwebb-kurser/oophp/me/kmom02/webroot/redovisning.php">Me-sida med denna redovisning och spelet på studentservern</a>. Alla synpunkter på lösningens detaljer är ytterst välkomna.</p>

<h2>Kmom03: SQL och databasen MySQL</h2>

<p>Jag har sedan tidigare en liten web-kurs i MySQL med komplexitet jämförbar med övningarna i detta kursavsnitt. Där använde jag MySQL Query Browser som klient mot lokal MySQL i XAMPP. Dessutom har jag ju sqlite övningarna i htmlphp sedan nyligen.</p>

<p>Jag var tvungen att logga in som root i mysql CLU för att få rättigheter att genomföra övningar där. Verktyget fungerade sedan men sannolikt blir det inte mycket arbete i denna miljö framöver. Access via webben och phpMyAdmin fungerade snabbt och utmärkt och stora delar av kursmomentets övningar utförde jag sedan i detta verktyg. Jag installerade inte MySql Workbench eftersom jag redan har ett alternativ i MySql Query Browser sedan tidigare.</p>

<p>I Cygwin efter SSH inloggning på studentservern var det trivialt att logga in till MySql CLU på BTH:s databasserver. Fantastiskt enkalt också via webben och phpMyAdmin. Lite trassligare med MySql Query Browser eftersom det inte finns någon dialog för SSH tunnling (likt WorkBench). Det löste sig efter en hel del arbete genom att i Cygwin öppna en SSH tunnel på studentservern från en port på min dator till databasservern. Därefter gick det fint att i verktyget logga in med databasens användaruppgifter via den aktuella porten på localhost.</p>

<p>SQL övningarna var en utmärkt uppfriskande repetition för mig. Jag gick igenom alla momenten och greppade allt hyfsat väl utom avsnitten om teckenkodning och storage engines, som jag något uppgivet fick lämna att plocka upp vid framtida behov.</p>

<p><a href="http://www.student.bth.se/~bjri15/dbwebb-kurser/oophp/me/kmom03/webroot/redovisning.php">Me-sida med denna redovisning på studentservern</a></p>

<h2>Kmom04: PHP PDO och MySQL</h2>

<p><strong>Uppgift 2:</strong> Jag skapade klassen CDatabase i mitt Branax och lade in nycklarna för konstruktorn i config.php. Det blev olika uppsättningar beroende på servermiljö och jag använder en lämplig SERVER variable för att välja rätt. </p>

<p><strong>Uppgift 3:</strong> Jag utgår från Mikaels version av den aktuella sidkontrollern. Jag skapar CMoviSearch klassen och flyttar till dess konstruktor all databashantering från sidkontrollern. Enkla getfunktioner hämtar sedan erforderlig output (formulär, sökresultat och radantal) som lagrats i klassvariabler. Ganska många sökparametrar att skicka över från sidkontrollern till klassen men jag hittade en trevlig funktion för att hantera detta. Klassen CHTMLTable fick sedan förbereda tabellen med sökresultatet. I detta fall lagrar konstruktorn nödiga visningsparametrar givna från start medan en getfunktion utför utskriftsförberedelsen med sökresultat från databasen som input. Av de funktioner som definierats i den ursprungliga sidkontrollern flyttade jag getQueryString till bootstrap, den används av båda klasserna och kan ju vara användbar i fler sammanhang. Övriga funktioner används bara i CHTMLTable och jag flyttar dem därför dit. Efter viss tvekan väljer jag att behålla initieringen av databasobjektet i sidkontrollern. På så sätt får ju sidkontrollern enkelt tillgång till dess debugfunktioner.</p>

<p><strong>Uppgift 4:</strong> Även här utgår jag helt från funktionaliteten i Mikaels övningsexempel. Klassen CUser får de funktioner som föreslås i uppgiften och jag rensar bort motsvarande kod från sidkontrollarana. Jag funderade en smula på att även låta klassen förbereda login och logut formulären, men avstod då jag inte fann dem tillräckligt allmängiltiga. Rätt eller fel?</p>

<p><strong>Extrauppgift:</strong> Även om detaljer för nya dynamiska get_navbar var en smula krångliga var principen helt klar. Inga större problem således med implementationen som sådan men en del CSS trassel blev det innan jag var någorlunda nöjd med resultatet. Jag har här samlat övningens alla sidkontrollrar i en submeny.</p>

<p><strong>Hur känns det att jobba med PHP PDO?:</strong> Med htmlphp i färskt minne känns detta helt naturligt.</p>

<p><strong>Gjorde du guiden med filmdatabasen, hur gick det?:</strong> Mycket trevlig guide att läsa men jag kodade bara valda delar. Alla olika CRUD funktioner körde jag nyligen under htmlphp.</p>

<p><strong>Du har nu byggt ut ditt Anax med ett par moduler i form av klasser, hur tycker du det konceptet fungerar så här långt, fördelar, nackdelar?:</strong> Jag tycker det fungerar mycket bra. Har dock en del funderingar kring vad som bäst hanteras av klasser eller sidkontrollrar.</p>

<p><a href="http://www.student.bth.se/~bjri15/dbwebb-kurser/oophp/me/kmom04/webroot/redovisning.php">Me-sida med denna redovisning på studentservern</a></p>

{$filter->markdown(<<<EOD
Kmom05: Lagra innehållet i databasen
----------------------------

**Uppgift 2:** Jag hämtade filtret enligt alternativ 1 och därefter var övningsuppgifterna relativt snabbt lösta. Testsidan är inte inlänkad till me-sidan men ligger tillgänglig i samma webroot. Fiffigt verktyg. 

**Uppgift 3:** Funktionerna för reset, create, update och delete är alla uppbyggda enligt samma princip med en formulärvisning och en utförandedel. Jag utgick från allt i sidokontrollrarna och lyfte först över databashanteringen till funktioner i klassen CContent enligt krav och bestämde mig strax för att göra klassfunktioner även för formulären. Triggad av minimalism i sidkontrollern flyttade jag till sist över även hanteringen av respektive central POST variabl till enkla men slutgiltigt publika klassfunktioner (men detta var kanske ändå var ett gå ett steg för långt?). Vidare behövdes en kompletterande funktion för att visa allt innehåll. Extrauppgiften med slugify är genomförd och används om create-formuläret skickar tom url eller slug. Jag lade även till ägarinformation i databasen med inloggad användare som default vid skapande av nytt innehåll.

**Uppgift 4:** Jag utgick från koden i guiden och lyfte över all initiering och funktionalitet till konstuktorn för klassen CPage. Enkla get-funktioner hämtar sedan 'main' och 'title' för mina Branaxvariabler för visning i sidkontrollern. För hämtning av efterfrågad page-information i databasen blev det en ny funktion i CContent. Enkelt och rakt fram. För extrauppgiften använde jag sedan ytterligare en ny CContent funktion för att förbereda submeny-items för alla innehållssidor till navbarens page-item. Här fick jag lägga ner en hel del arbete för att förstå och justera den dynamiska navbaren för önskad funktion.

**Uppgift 5:** CBlog och dess sidkontroller implementerades helt analogt med CPage. Alla extrauppgifter är även här implementerade.

**Det blir en del moduler till ditt Anax nu, hur känns det?:** Jag ställer mig frågan om allt innehåll verkligen passar i ett ramverk eller om det kanske är för mycket site-specifik kod i klasserna? Jag är tacksam för alla synpunkter kring detta i mina lösningar.

**Berätta hur du tänkte när du löste uppgifterna, hur tänkte du när du strukturerade klasserna och sidkontrollerna?:** Jag siktar numer på relativt minimala sidkontrollrar. I fallet CContent gick det som sagt kanske till överdrift med en ganska omfattande och något spretig class som resultat. 

**Börjar du få en känsla för hur du kan strukturera din kod i klasser och moduler, eller kanske inte?:** Jag tycker inte alltid det blir bra. Är det rätt att gå för minimal sidkontroller eller har jag gått för långt? Synpunkter på mina lösningar även i detta avseesnde tas tacksamt emot.

**Snart har du grunderna klara i ditt Anax, grunderna som kan skapa många webbplatser, är det något du saknar så här långt, kanske några moduler som du känner som viktiga i ditt Anax?:** Vi har ju bildhantering i nästa kursmoment.

**Kursmomentet som helhet:** Ett tufft kursmoment som i mitt fall tagit betydligt mer än en veckas halvtid i ansprråk, det har ju varit ovanligt omfattande med väldigt många krav och extrauppgifter. Onödigt länge brottades jag också med två egenheter hos studentservern. Till skillnad från min lokala server är den känslig för stor bokstav i tabellnamn vilket gav mycket konstiga resultat. Dessutom tycks den beröva .sql filer (för återställning) läsrättigheter vid publicering vilket sannerligen heller inte var lättfunnet. Det återstår nu måhända en del uppstädning om man vill ha riktigt bra och snygg funktionalitet, men det får anstå tills vidare. Denna redovisning är fö skrivet i markdown.

[Me-sida med denna redovisning på studentservern](http://www.student.bth.se/~bjri15/dbwebb-kurser/oophp/me/kmom05/webroot/redovisning.php)

Kmom06: Bildbearbetning och galleri
-----------
**Uppgift 1:** Jag utgick från koden i guiden och flyttade över den i allt väsentligt till CImage klassens konstruktor. Endast de två konstantdeklaraionerna blir kvar i img.php samt anropet till konstruktorn. (Är det månne förkastlit att låta klassen läsa globalkonstanter på detta vis och bättre att skicka över informationen som parametrar istället? **kommentar mottages tacksamt**.) Samtliga extrauppgifter är lösta: transparens, gif som input och output, samt filter för grayscale och sepia. Filtreringen erhålles genom att sätta GET-variabeln 'filter' till antingen 'grs' eller 'sep' och utföres med upprepade anrop av funktionen imagefilter() med parametrar hårdkodade enligt förslag i Mikael Roos artikeln om CImage.

**Uppgift 2:** Även här utgick jag från koden i guiden och flyttade den i allt väsentligt till CGallery klassens konstruktor. Två klassvaribler håller output av själva galleriet resp breadcrumb vilka båda skrivs ut av enkla get-funktioner från sidkontrollern. Även i detta fall görs konstantdeklarationer i sidkontrollern för användning i klassen. Jag bökade en del med att bli av med en av dessa, som tycktes överflödig, men det var besvärligare än vad det smakade och jag gav upp försöket. Breadcrumbfunktionen kan ju tyckas allmänt användbar och därför förtjäna en egen klass för sig, men jag lät den ändå göra övrig funktioanlitet sällskap i CGallery.

**Extrauppgifter:** Utförda. Intressant läsning om headers och cacheing. Hade varit bra att läsa innan jag försökte förtå vad som händer i img.php.

**Hade du erfarenheter av bildhantering sedan tidigare?** Nej inte på den här nivån. Det finns ju program som sköter sådant till vardags.

**Hur känns det att jobba i PHP GD?** Förvånande enkelt, i vart fall när jag som här får recepten serverade.

**Hur känns det att jobba med img.php, ett bra verktyg i din verktygslåda?** Absolut, det hade tex sparat en hel del arbete med att snickra till bilder för htmlphp.

**Detta var sista kursmomentet innan projektet, hur ser du på ditt Anax nu, en summering så här långt?** Ramverket känns jättebra. Dock har jag fortfarande lite problem att greppa om vad som ingår. Jag har nämligen svårt att betrakta alla klasser jag skapat som delar av ett generellt ramverk eftersom de exempelvis hanterar väldigt spcifika databaser eller tabeller. **Jag tar mycket gärna emot lite feedback på denna fundering**  

**Finns det något du saknar så här långt, kanske några moduler som du känner som viktiga i ditt Anax?** Ljud och rörliga bilder kanske? meddelandetjänster? funktioner kring handel? 

[Me-sida med denna redovisning på studentservern](http://www.student.bth.se/~bjri15/dbwebb-kurser/oophp/me/kmom06/webroot/redovisning.php)

Kmom07/19: ....
---------------
Redovisningstext

EOD
)}
       
{$branax['byline']}

</article>

EOD;


// Finally, leave it all to the rendering phase of Brnax.
include(BRANAX_THEME_PATH);
