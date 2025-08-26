
## Template:Film

MediaWiki’de `Template:Film` sayfasını oluşturmak için şu adımları izle:

---

## Adım 1: Tarayıcıda Template Sayfasına Git

Adres çubuğuna aşağıdakini yaz:

```
http://mediawiki.local/index.php?title=Template:Film&action=edit
```

> Burada `mediawiki.local` senin MediaWiki kurulumunun domaini veya localhost olabilir.

---

## Adım 2: İçeriği Yapıştır

Açılan editör sayfasına daha önce verdiğim `Template:Film` içeriğini yapıştır:

```wiki
'''Title''': {{{Title|}}}
'''Director''': {{{Director|}}}
'''Release Year''': {{{ReleaseYear|}}}
'''Genre''': {{{Genre|}}}

{{#set:
 | Title = {{{Title|}}}
 | Director = {{{Director|}}}
 | ReleaseYear = {{{ReleaseYear|}}}
 | Genre = {{{Genre|}}}
}}
```

---

## Adım 3: Sayfayı Kaydet

Açıklama kısmına “Create Film template” gibi bir not yazabilir ve “Sayfayı Kaydet” butonuna tıkla.

---

## Böylece:

* `Template:Film` oluşturulmuş olur.
* Page Forms, oluşturduğun formda bu şablonu kullanır.

---


## Form:Film

---

## Adım 1: Form Sayfasına Git

Tarayıcı adres çubuğuna yaz:

```
http://mediawiki.local/index.php?title=Form:Film&action=edit
```

---

## Adım 2: İçeriği Yapıştır

Aşağıdaki kodu yapıştır:

```wiki
{{{for template|Film}}}

Title: {{{field|Title|mandatory}}}

Director: {{{field|Director|mandatory}}}

Release Year: {{{field|ReleaseYear|input type=number|min=1888|max=2100}}}

Genre: {{{field|Genre|input type=dropdown|values=Drama,Comedy,Action,Sci-Fi,Animation}}}

{{{end template}}}
```

---

## Adım 3: Sayfayı Kaydet

Açıklama kısmına “Create Film form” yazıp “Sayfayı Kaydet” butonuna tıkla.

---

## Adım 4: Test Et

Şimdi yeni film sayfası oluşturmak için şuraya git:

```
http://mediawiki.local/index.php?title=Film:Inception&action=formedit
```

Burada form açılacak, bilgileri doldurup sayfayı oluşturabilirsin.

---

İstersen sonraki adımda **sayfaları listeleyen sorgu sayfası** veya diğer gelişmiş PageForms özelliklerine geçebiliriz. Yardımcı olayım mı?
