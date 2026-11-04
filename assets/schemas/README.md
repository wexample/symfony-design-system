# Schemas

`web/` is a copy, whole, of the `web/` directory of the stack's `json-schema`
service — the norm describing web form fields, the form holding them and what
a form answers. It is never edited here: a change is made in the norm, then
copied again with `design-system:schemas:sync <path to the norm's web/>`;
`--check` says whether the copy still matches. Beside each field component,
`<x>.schema.json` points into this copy, which is how the registry knows the
component carries its contract.
