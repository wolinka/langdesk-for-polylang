🇬🇧 **English** | 🇹🇷 [Türkçe](README-tr.md)

---

# LangDesk: Translation Roles for Polylang

Assign languages to WordPress users so each translator edits content **only in
their own language**, while still being able to read the source language to
translate from it. Built on the free
[Polylang](https://wordpress.org/plugins/polylang/) plugin.

LangDesk turns a multilingual Polylang site into a tidy workspace for translation
teams: no more "every language in one Posts list" chaos, and no accidental edits
to a language a translator does not own.

## Features

- **Per-language editing.** A user can edit only the languages assigned to them.
  Other languages stay readable but cannot be changed.
- **Enforced at the capability level.** The restriction runs through
  `map_meta_cap`, so it also covers the block editor / REST API, quick edit, bulk
  edit and page builders (Elementor, Divi), not just the classic edit screen.
- **Fail-closed.** If a post's language cannot be determined, editing is denied
  rather than allowed. An access-control leak is worse than none.
- **A clean working view.** A restricted translator's post lists default to their
  own language. A "To translate into X" view lists the source posts still missing
  that translation, and an "All in {source}" view shows the full source list to
  translate from.
- **Composes with your roles.** LangDesk only restricts by language. Whether a
  user can publish or only submit for review still follows their WordPress role.
  Site managers (administrators) are never restricted.
- **Graceful degradation.** If Polylang is not active, LangDesk adds no
  restrictions and shows an admin notice instead of failing.

## Requirements

- WordPress 6.0 or newer
- PHP 7.4 or newer
- [Polylang](https://wordpress.org/plugins/polylang/) (free), installed and
  active, with your languages configured

## Installation

1. Install and activate Polylang, and configure your languages.
2. Install LangDesk and activate it.
3. Edit a user's profile and choose their allowed languages under **LangDesk:
   Translation Languages**.

Give translators a non-administrator role such as Editor, Author or Contributor.
LangDesk never restricts users who can manage the whole site.

## How it works

A user with no assigned language is unrestricted. Once at least one language is
assigned (and the user is not a site manager) they become a restricted translator:

- They can **read** any language, including the source, so they can translate
  from it.
- They can **write** only their assigned language(s). Attempts to edit, delete or
  publish a post in another language are denied at the capability level.
- New content they create is pinned to their own language.
- Language assignment can only be changed by users who can edit other users, so a
  translator cannot grant themselves extra languages.

The only data stored is a single user meta key (`langdesk_allowed_langs`);
uninstalling removes it. No custom tables, no options, no external services, no
tracking.

## Frequently asked questions

**Does it need Polylang Pro?**
No. It works with both the free Polylang and Polylang Pro.

**Can a translator still see the source content?**
Yes. Reading is never blocked; only writing other languages is.

**A translator can still edit everything. Why?**
LangDesk never restricts site managers (administrators / `manage_options`). Give
the translator a role such as Editor, Author or Contributor.

## Contributing

This repository mirrors the distributed plugin, so changes pushed directly here
are overwritten on the next sync. Found a bug or have an idea? Please open an
issue to discuss it.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

---

Built by [Özlem Çimen](https://www.linkedin.com/in/ozlemcimen/). Enterprise
WordPress consulting at [Wolinka](https://wolinka.com.tr).
