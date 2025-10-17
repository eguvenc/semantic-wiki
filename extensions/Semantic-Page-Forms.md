
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
composer require mediawiki/page-forms "~5.4"
```

Then add the following line to your `LocalSettings.php`:

```php
#
# Page Forms Extension
#
wfLoadExtension( 'PageForms' );
```

```
php maintenance/update.php
```

---

## Testing the Installation

### Example: Simple "Person" Form

1. **Create a template**: `Template:Person`

http://mediawiki.local/index.php/Template:Person

```wiki
<includeonly>
{{#set: FirstName={{{First name|}}}; LastName={{{Last name|}}}; DateOfBirth={{{Date of birth|}}} }}
[[Category:Person]]
<!-- display section -->
'''Name:''' {{{FirstName}}} {{{LastName}}}<br/>
'''Date of Birth:''' {{{DateOfBirth}}}
</includeonly>
```

2. **Create a form**: `Form:Person`

http://mediawiki.local/index.php/Form:Person

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
   
👉 http://mediawiki.local/index.php/James_Brown

CLick to Create a Page.

```wiki
{{Person
|FirstName=James
|LastName=Brown
|DateOfBirth=1933-05-03
}}
```
- Result

James Brown
Jump to navigationJump to search
Name: James Brown
Date of Birth: 1933-05-03

Category: Person

4. When editing a existing page, go to:

👉 http://mediawiki.local/index.php/Special:FormEdit/Person/James_Brown

You can then review the semantic data on the page:

👉 http://mediawiki.local/index.php/Special:Browse

---

## Verification

* http://mediawiki.local/index.php/Special:Version → Do PageForms and Semantic MediaWiki appear in the list ? ✅ / ❌
* http://mediawiki.local/index.php/Special:FormEdit/Person/PersonName  → Does the form work correctly ? ✅ / ❌
* http://mediawiki.local/index.php/Special:Browse/PageName → Were the semantic properties stored successfully ? ✅ / ❌
