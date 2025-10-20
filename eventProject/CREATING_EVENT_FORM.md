
Wikimedia 1.39  ve Wikibase 1.39 sürümü aşağıdaki bileşenleri kullanarak bir event formu oluşturmak isitiyorum. Aşağıdaki alanlar olacak. Event modülü için daha sonra arama ve filtreleme yapılabilecek ve Event lar listelenebilecek.

Bileşen listesi:
-----------------------

SemanticMediawiki (SMW), SemanticResultFormats, Maps, PageForms, Arrays, Variables, Loops, ParserFunctions, WikibaseEdtf (extended datetime format), WikibaseLocalMedia, WikibaseManifest, Babel, EntitySchema, OAuth, SemanticWikibase, Tweeki (bootstrap) skin

İşte form detayları:

----------------------
CREATE EVENT:

Acronym:  TEXT FIELD
(If commonly used, enter the acronym of the event. Usually the event's year is used in combination with the acronym.)

Title:  TEXT FIELD
(Enter the complete office title of the event)

Ordinal:  TEXT FIELD
(E.g. 1 for a new conference, i. e. the first conference in a series. Use only numbers without dots or 'st', 'nd', 'rd' etc.)

Event Series:  AUTO COMPLETE AREA
(Enter the name of the event series this event belongs to and select the correct one from the suggestions. If you receive no suggestions after entering the event series name, it was probably not yet captured in ConfIDent. In this case please enter the event series here first. For a more detailed definition of the term event series see https://tibhannover.github.io/ConfIDent_schema/EventSeries/

An academic event series describes the set of academic events which take place on a regular basis and thus have an established common identity. This identity is constituted, for example, through institutional continuity in the hosting of a series (e.g. by a specialised society), thematic focuses and/or a common label under which a series is defined (particularly name and acronym). Nevertheless, it is possible that each of these criteria may change over time.


Single Day Event: RADIO BUTTON  yes -  no
Choose 'yes' if the event only lasts for one day (or less). If the event spans multiple days choose 'no'.


Start Date:  DATE (DD.MM.YYYY) 
You can either enter the exact date or only select a year if for example the exact date is not yet known.


End Date:  (DD.MM.YYYY)
You can either enter the exact date or only select a year if for example the exact date is not yet known.