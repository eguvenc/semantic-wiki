
## Template:Film

To create the `Template:Film` page in MediaWiki, follow these steps:

---

## Step 1: Go to the Template Page in Your Browser

Enter the following in your browser’s address bar:

```
http://mediawiki.local/index.php?title=Template:Film&action=edit
```

> Here, `mediawiki.local` should be replaced with your MediaWiki domain or `localhost`.

---

## Step 2: Paste the Content

Paste the following content into the editor page:

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

## Step 3: Save the Page

Add a summary like “Create Film template” and click the “Save page” button.

---

## This Will:

* Create the `Template:Film` page.
* Allow Page Forms to use this template in your forms.

---

## Form:Film

---

## Step 1: Go to the Form Page

Enter this in your browser’s address bar:

```
http://mediawiki.local/index.php?title=Form:Film&action=edit
```

---

## Step 2: Paste the Content

Paste the following code:

```wiki
{{{for template|Film}}}

Title: {{{field|Title|mandatory}}}

Director: {{{field|Director|mandatory}}}

Release Year: {{{field|ReleaseYear|input type=number|min=1888|max=2100}}}

Genre: {{{field|Genre|input type=dropdown|values=Drama,Comedy,Action,Sci-Fi,Animation}}}

{{{end template}}}
```

---

## Step 3: Save the Page

Add a summary like “Create Film form” and click “Save page.”

---

## Step 4: Test the Form

Now, to create a new film page, go to:

```
http://mediawiki.local/index.php?title=Film:Inception&action=formedit
```

The form will open, allowing you to fill in the information and create the page.

---

If you want, we can move on to the **query pages that list pages** or other advanced PageForms features next.

---

I can also translate that next part about queries and advanced PageForms if you want. Do you want me to?
