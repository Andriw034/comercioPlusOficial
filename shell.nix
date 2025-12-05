{ pkgs ? import <nixpkgs> {} }:

pkgs.mkShell {
  buildInputs = [
    pkgs.php
    pkgs.phpPackages.composer
    pkgs.gnutar
    pkgs.gzip
    pkgs.git
    pkgs.nodejs
  ];

  shellHook = ''
    echo "Entorno ComercioPlus listo"
    echo "php: $(php -v | head -n 1 || true)"
    echo "composer: $(composer --version 2>/dev/null || true)"
    echo "tar: $(tar --version 2>/dev/null || true)"
  '';
}
