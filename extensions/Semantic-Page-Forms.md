
## PageForms

PageForms is an extension developed for MediaWiki, and its purpose is to simplify **structured data entry** and **page creation**.

MediaWiki version: **1.39 LTS** or higher
Semantic MediaWiki compatible version: **4.x**
PageForms compatible version: **5.x**

### What is its main function?

* Normally, in MediaWiki, you create a page by directly writing wikitext.
* PageForms provides users with an easy and user-friendly **form-based interface**.
* These forms allow users to enter data without manually writing complex templates or semantic data structures (for Semantic MediaWiki).

### Why is it important?

* When used together with Semantic MediaWiki, the data entered into pages is automatically transformed into **semantic properties**.
* By filling in fields in the form, users automatically generate the correct templates and semantic annotations in the background.
* This reduces errors and ensures data consistency.

---

## Installing PageForms

```sh
composer require mediawiki/page-forms
```

Then add the following line to your `LocalSettings.php`:

```php
wfLoadExtension( 'PageForms' );
```

---

## Testing the Installation

### Example: Simple "Person" Form

1. **Create a template**: `Template:Person`

```wiki
First name: {{{|First name}}}
Last name: {{{|Last name}}}
Date of birth: {{{|Date of birth}}}
```

2. **Create a form**: `Form:Person`

```wiki
{{{for template|Person}}}
! First name
{{{field|First name}}}
! Last name
{{{field|Last name}}}
! Date of birth
{{{field|Date of birth|input type=date}}}
{{{end template}}}
```

3. When adding a new page, go to:
   👉 `http://localhost/mediawiki/index.php/Special:FormEdit/Person`

With this form, you can create new "Person" pages using a **visual form interface**.

---

## Adding Semantic Properties

If you want to add semantic data to persons, extend the `Template:Person` as follows:

```wiki
[[Has first name::{{{|First name|}}}]]
[[Has last name::{{{|Last name|}}}]]
[[Date of birth::{{{|Date of birth|}}}]]
```

You can then review the semantic data on the page:
👉 `Special:Browse`

---

## Verification

* `Special:Version` → Do PageForms and Semantic MediaWiki appear in the list?
* `Special:FormEdit/Person` → Does the form work correctly?
* `Special:Browse/PageName` → Were the semantic properties stored successfully?

---

Would you like me to also translate the example labels ("First name", "Last name", etc.) into **more wiki-friendly field names** (e.g., `Given name`, `Family name`), or should I keep them exactly as-is?
