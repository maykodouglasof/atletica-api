module.exports = ({ env }) => ({
  defaultConnection: 'default',
  connections: {
    default: {
      connector: 'bookshelf',
      settings: {
        client: 'postgres',
        host: env('DATABASE_HOST', 'ec2-54-146-4-66.compute-1.amazonaws.com'),
        port: env.int('DATABASE_PORT', 5432),
        database: env('DATABASE_NAME', 'd41s94sicrgli1'),
        username: env('DATABASE_USERNAME', 'lkdeiisokowkif'),
        password: env('DATABASE_PASSWORD', '827ccf30b93ff3e46333d8285b67228f305e26f9f1549c937a3ca864ff8b1b6c'),
      },
      options: {
        ssl: false,
      },
    },
  },
});