# Releasing

Releases are Git tags on `main`. Do not tag feature branches.

## Preflight

Before tagging:

```sh
git checkout main
git pull --ff-only
make ci
```

The local `make ci` target runs in Docker and performs Composer validation,
dependency installation, PHP CS Fixer, PHPStan, and PHPUnit.

## Versioning

Use SemVer-style tags with a `v` prefix:

```text
v0.5.0
v0.5.1
v1.0.0
```

Patch tags are for bug fixes and compatibility fixes. Minor tags are for new
bundle functionality or documentation milestones before `1.0.0`.

## Tagging

Create and push the tag from `main`:

```sh
git tag v0.5.0
git push origin v0.5.0
```

If a tag was created on the wrong commit, do not move it silently after it has
been pushed. Create a corrected patch tag instead, or clearly coordinate the
tag replacement before deleting and recreating it.

## GitHub Release

Create a GitHub release from the tag and include:

- The version number.
- A short summary of user-visible changes.
- Compatibility notes when PHP, Symfony, or AMQP client constraints changed.
- Upgrade notes when service IDs, configuration, or public contracts changed.

Use `.github/RELEASE_TEMPLATE.md` as the release body checklist.

## Packagist

Before the first Packagist publish:

- Ensure the GitHub repository is public.
- Ensure `composer.json` has the final package name:
  `sigbits/php-amqp-client-bundle`.
- Ensure the license is tracked in `LICENSE`.
- Ensure the package description, homepage, support URLs, and keywords are
  accurate.
- Ensure the intended release tag is present on `main`.

Then submit the GitHub repository to Packagist. After Packagist accepts the
package, configure automatic updates from GitHub so future tags become
available without manual Packagist updates.

## Post-Release Check

After publishing, verify the package can be required from a clean Symfony
application:

```sh
composer require sigbits/php-amqp-client-bundle:^0.5
```

Then add a minimal `config/packages/sigbits_amqp.yaml` and confirm the Symfony
container can compile.
