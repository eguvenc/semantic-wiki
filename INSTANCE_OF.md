
## INSTANCE OF NEDİR ?


Wikibase dünyasında her “property (özellik)” ve her “item (varlık)” bir **Q veya P kodu** ile temsil edilir.

Senin örneğin:

```
instance of (P1) = Q5
```

Bunu adım adım açalım:

---

### 1️⃣ `instance of (P1)`

* `P1` burada **property (özellik)** kodu.
* “instance of” demek: bu varlık hangi sınıfa aittir, yani hangi kategoriye/nesne tipine örnektir.
* Örnek: bir şehir, bir kişi veya bir kitap olabilir.

---

### 2️⃣ `= Q5`

* `Q5` ise **item (varlık)** kodudur.
* Q kodları Wikidata/Wikibase’de somut varlıkları temsil eder.
* Örnek:

  * Q5 = “human” (insan)
  * Q1 = “universe” (evren)
  * Q2 = “earth” (dünya)

---

### 3️⃣ Birlikte anlamı

```
instance of (P1) = Q5
```

* Bu, “bu varlık **insan sınıfına aittir**” demektir.
* Yani, sorguladığında sadece **insan olan item’lar** döner.

---

💡 Özet:

* **Property (P1)** → “hangi kategoriye ait?”
* **Item (Q5)** → “insan”
* `haswbstatement:P1=Q5` → “instance of insan olan her şeyi getir”

---

İstersen sana diğer property’lerin (`subclass of`, `part of` vb.) anlamlarını da tek tek açıklayabilirim. Bunu yapmamı ister misin?


| Property               | P Kodu | Anlamı / Kullanımı                                                                                         |
| ---------------------- | ------ | ---------------------------------------------------------------------------------------------------------- |
| instance of            | P1     | Bu item hangi sınıfa aittir, hangi kategoriye örnektir. Örn: “Bu bir insan” (Q5).                          |
| subclass of            | P2     | Bu item bir sınıfın alt sınıfıdır. Örn: “Kedi” (Q2) “Hayvan” (Q1) alt sınıfıdır.                           |
| subproperty of         | P3     | Bu property başka bir property’nin alt özelliğidir. Örn: “has mother” P3 “has parent” özelliğinin altıdır. |
| title                  | P4     | Bu item’in unvan, başlık veya isim bilgisidir.                                                             |
| has part               | P5     | Bu item’in hangi parçalardan oluştuğunu belirtir.                                                          |
| part of                | P6     | Bu item hangi daha büyük parçanın parçasıdır.                                                              |
| number of parts        | P7     | Bu item’in kaç parçası olduğunu belirtir.                                                                  |
| event                  | P8     | Bu item bir etkinlik veya olay ile ilişkilidir.                                                            |
| took place at          | P9     | Olayın gerçekleştiği yer.                                                                                  |
| start date             | P10    | Olayın veya varlığın başlangıç tarihi.                                                                     |
| end date               | P11    | Olayın veya varlığın bitiş tarihi.                                                                         |
| date                   | P12    | Genel tarih bilgisi.                                                                                       |
| participant            | P13    | Olay veya süreçte yer alan kişi veya grup.                                                                 |
| carried out by         | P14    | Bir iş veya etkinliği gerçekleştiren kişi/grup.                                                            |
| transferred title to   | P15    | Unvanın, mülkiyetin devredildiği kişi/item.                                                                |
| transferred title from | P16    | Unvanın, mülkiyetin alındığı kişi/item.                                                                    |
| moved to               | P17    | Bir item’in taşındığı yer.                                                                                 |
| moved from             | P18    | Bir item’in taşındığı yerin önceki konumu.                                                                 |
| custody surrendered by | P19    | Varlık veya item’in teslim edildiği kişi/grup.                                                             |
| custody received by    | P20    | Varlık veya item’in alındığı kişi/grup.                                                                    |
| placeholder            | P21    | Geçici yer tutucu olarak kullanılır.                                                                       |
| material used          | P22    | Bu item’in üretiminde kullanılan malzeme.                                                                  |
| consists of            | P23    | Bu item’in hangi parçalar veya bileşenlerden oluştuğu.                                                     |
| bears feature          | P24    | Bu item’in sahip olduğu özellik veya karakteristik.                                                        |
| inscription            | P25    | Üzerindeki yazı, yazıt veya etiket.                                                                        |
| condition              | P26    | Item’in durumu, fiziksel veya işlevsel hali.                                                               |
| part removed           | P27    | Item’den çıkarılmış parça.                                                                                 |
| part added             | P28    | Item’e eklenmiş parça.                                                                                     |
| resulted in            | P29    | Bu item’in hangi sonucu doğurduğu.                                                                         |
| used for               | P30    | Bu item’in kullanım amacı.                                                                                 |
| dimension              | P31    | Genel ölçü bilgisi.                                                                                        |
| height                 | P32    | Yükseklik.                                                                                                 |
| length                 | P33    | Uzunluk.                                                                                                   |
| width                  | P34    | Genişlik.                                                                                                  |
| weight                 | P35    | Ağırlık.                                                                                                   |
| address                | P36    | Adres bilgisi.                                                                                             |
| coordinate location    | P37    | Coğrafi koordinatlar (enlem/boylam).                                                                       |
| location               | P38    | Konum veya yer bilgisi.                                                                                    |
| position               | P39    | Belirli pozisyon veya görev.                                                                               |
| by mother              | P40    | Annesi tarafından ilişkili.                                                                                |
| from father            | P41    | Babası tarafından ilişkili.                                                                                |
| spouse                 | P42    | Eş ilişkisi.                                                                                               |
| in custody of          | P43    | Hangi kişi/grup tarafından tutuluyor.                                                                      |
| owner                  | P44    | Sahip.                                                                                                     |
| right held by          | P45    | Hakların sahibi.                                                                                           |
| curator                | P46    | Koruyucu veya düzenleyen kişi.                                                                             |
| sibling of             | P47    | Kardeş ilişkisi.                                                                                           |
| parent of              | P48    | Ebeveyn ilişkisi.                                                                                          |
| sex or gender          | P49    | Cinsiyet bilgisi.                                                                                          |
| affiliation            | P50    | Bağlı olduğu kurum, grup veya organizasyon.                                                                |
