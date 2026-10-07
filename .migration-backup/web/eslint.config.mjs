import { defineConfig, globalIgnores } from "eslint/config";
import { fixupConfigRules, fixupPluginRules } from "@eslint/compat";
import react from "eslint-plugin-react";
import preferArrowFunctions from "eslint-plugin-prefer-arrow-functions";
import prettier from "eslint-plugin-prettier";
import typescriptEslint from "@typescript-eslint/eslint-plugin";
import globals from "globals";
import tsParser from "@typescript-eslint/parser";
import path from "node:path";
import { fileURLToPath } from "node:url";
import js from "@eslint/js";
import { FlatCompat } from "@eslint/eslintrc";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const compat = new FlatCompat({
    baseDirectory: __dirname,
    recommendedConfig: js.configs.recommended,
    allConfig: js.configs.all
});

export default defineConfig([globalIgnores([
    "**/.next",
    "**/public",
    "**/node_modules",
    "**/yarn.lock",
    "**/package-lock.json",
    "**/*.test.js",
    "**/coverage",
]), {
    extends: fixupConfigRules(compat.extends(
        "eslint:recommended",
        "plugin:react/recommended",
        "plugin:import/errors",
        "plugin:import/warnings",
        "plugin:jsx-a11y/recommended",
        "plugin:react-hooks/recommended",
        "airbnb",
        "eslint-config-prettier",
        "plugin:@next/next/recommended",
        "plugin:@typescript-eslint/recommended",
    )),

    plugins: {
        react: fixupPluginRules(react),
        "prefer-arrow-functions": preferArrowFunctions,
        prettier,
        "@typescript-eslint": fixupPluginRules(typescriptEslint),
    },

    languageOptions: {
        globals: {
            ...globals.browser,
            ...globals.commonjs,
            ...globals.node,
        },

        parser: tsParser,
        ecmaVersion: 2020,
        sourceType: "module",

        parserOptions: {
            ecmaFeatures: {
                jsx: true,
            },
        },
    },

    settings: {
        react: {
            version: "detect",
        },

        "import/resolver": {
            typescript: {
                directory: "./tsconfig.json",
            },

            node: {
                extensions: [".js", ".jsx", ".ts", ".tsx"],
            },
        },
    },

    rules: {
        "jsx-a11y/control-has-associated-label": "off",
        "react/react-in-jsx-scope": "off",
        "import/no-duplicates": "error",
        "import/no-unresolved": "error",
        "import/named": "error",

        "prettier/prettier": ["error", {
            endOfLine: "auto",
        }],

        "react/jsx-filename-extension": [1, {
            extensions: [".tsx"],
        }],

        "react/state-in-constructor": "off",
        "react/prop-types": "off",
        "react/no-access-state-in-setstate": "error",
        "react/no-danger": "off",
        "react/no-did-mount-set-state": "error",
        "react/no-did-update-set-state": "error",
        "react/no-will-update-set-state": "error",
        "react/no-redundant-should-component-update": "error",
        "react/no-this-in-sfc": "error",
        "react/no-typos": "error",
        "react/no-unused-state": "error",
        "react/jsx-no-bind": "error",
        "no-useless-call": "error",
        "no-useless-computed-key": "error",
        "no-useless-concat": "error",
        "no-useless-constructor": "error",
        "no-useless-rename": "error",
        "no-useless-return": "error",
        "react/jsx-props-no-spreading": "off",
        "react-hooks/exhaustive-deps": "off",

        "react/self-closing-comp": ["error", {
            component: true,
            html: true,
        }],

        "no-constant-condition": ["error", {
            checkLoops: false,
        }],

        "no-console": ["error", {
            allow: ["log", "warn", "error"],
        }],

        "no-unused-vars": "off",
        "@typescript-eslint/no-unused-vars": ["error"],
        "no-underscore-dangle": ["error"],

        "prefer-arrow-functions/prefer-arrow-functions": ["warn", {
            classPropertiesAllowed: false,
            disallowPrototype: false,
            returnStyle: "unchanged",
            singleReturnOnly: false,
        }],

        "react/function-component-definition": "off",
        "react/button-has-type": "off",
        "import/no-extraneous-dependencies": "off",

        "import/extensions": ["error", "ignorePackages", {
            ts: "never",
            tsx: "never",
        }],

        "react/require-default-props": "off",
        "import/prefer-default-export": "off",
        "no-restricted-exports": "off",
        "react/no-unescaped-entities": "off",
        "no-shadow": "off",
        "@typescript-eslint/no-shadow": "error",
    },
}]);