const allowedTypes = [
    'feat',
    'fix',
    'chore',
    'docs',
    'refactor',
    'test',
    'build',
    'ci',
    'perf',
    'style',
];

export default {
    defaultIgnores: false,
    parserPreset: {
        parserOpts: {
            headerPattern: /^(\w+): (.+)$/,
            headerCorrespondence: ['type', 'subject'],
        },
    },
    rules: {
        'header-max-length': [2, 'always', 72],
        'subject-empty': [2, 'never'],
        'type-case': [2, 'always', 'lower-case'],
        'type-empty': [2, 'never'],
        'type-enum': [2, 'always', allowedTypes],
    },
};
