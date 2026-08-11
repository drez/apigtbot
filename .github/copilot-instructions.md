# Copilot Instructions

Project context:
- PHP app using Propel 1 and GoatCheese behaviors.
- Schemas live in .admin/*.schema.xml and build is run via ./gc b.

Guidelines:
- Prefer minimal, targeted changes and preserve existing code style.
- Do not edit vendor/ except when explicitly asked.
- All Built/ folders contain generated code and should not be customized. Changes to Built code must be made in the Builder "gc".
- Most customizations should be made through the related Wrapper file via hooks.
- .admin/*.schema.xml files are generated from the related .hjson files. Only edit the .hjson files.

Build tips:
- Use ./gc b for full builds.
- If schema changes are made, ensure database name is consistent across schema files.
