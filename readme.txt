=== LangDesk: Translation Roles for Polylang ===
Contributors:      wolinka
Tags:              polylang, multilingual, translation, user roles, capabilities
Requires at least: 6.0
Tested up to:      7.0
Requires PHP:      7.4
Stable tag:        1.0.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Assign languages to editors so each translator works only in their own language, with a clean per-language view. Built for Polylang.

== Description ==

LangDesk turns a multilingual Polylang site into a tidy workspace for translation
teams. Assign one or more languages to a user and they can only edit content in
those languages, while still being able to read the source language to translate
from it.

* **Per-language editing.** A translator can edit only the languages assigned to
  them. Other languages stay readable but cannot be changed (fail-closed: enforced
  at the capability level, so it also covers the block editor / REST API, quick
  edit, bulk edit and page builders).
* **A clean working view.** A restricted translator's post lists default to their
  own language, so their content is always front and centre, instead of the
  all-languages mix. A "To translate into X" view filters to the source-language
  posts, where Polylang's own "+" creates the translation.
* **Composes with your roles.** LangDesk only restricts by language. Whether a
  user can publish or only submit for review still follows their WordPress role.
  Site managers (manage_options) are never restricted.

Requires the free [Polylang](https://wordpress.org/plugins/polylang/) plugin.

LangDesk is fully free, with no paywalled features. It is built and maintained by
[Wolinka](https://wolinka.com.tr).

== Installation ==

1. Install and activate Polylang, and configure your languages.
2. Install LangDesk: Plugins > Add New > Upload, then activate.
3. Edit a user's profile and choose their allowed languages under "LangDesk:
   Translation Languages".

Give translators a non-administrator role such as Editor, Author or Contributor.
LangDesk never restricts site managers (anyone who can manage the whole site, like
an Administrator), so a translator on an Administrator role is not limited by
language.

== Frequently Asked Questions ==

= Does it work with the free Polylang or only Polylang Pro? =

It works with the free Polylang. LangDesk relies only on Polylang's public API.

= What happens to a user with no language assigned? =

Nothing changes for them. A user is restricted only once at least one language is
assigned. Site managers (manage_options) are never restricted.

= Can a translator still see the source content to translate from it? =

Yes. Reading is never blocked; only writing other languages is. A "To translate
into X" view lists the source-language posts, where Polylang's own "+" creates the
translation.

= My translator can still edit every language. Why? =

LangDesk never restricts site managers, that is any user who can manage the whole
site (Administrators and anyone with the manage_options capability). Give the
translator a role such as Editor, Author or Contributor instead of Administrator.

== Screenshots ==

1. Assigning allowed languages on a user's profile.
2. A translator's post list, filtered to their own language, with the "To translate into X" view.
3. The "All in {source}" reference view, listing every source-language post to translate from.

== Changelog ==

= 1.0.0 =
* Initial release: per-language edit restriction, user language assignment,
  to-translate queue.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
