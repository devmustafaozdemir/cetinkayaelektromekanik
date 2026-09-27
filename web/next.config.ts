import type { NextConfig } from 'next';

const nextConfig: NextConfig = {
  output: 'standalone',
  poweredByHeader: false,
  agentRules: false,
  // new value on every build → browsers fetch the fresh CSS/JS after a deploy
  env: { ASSET_VERSION: String(Date.now()) },
  turbopack: { root: __dirname },
  async redirects() {
    return [
      { source: '/servis-talebi', destination: '/teklif-al', permanent: true },
      { source: '/servis-takip', destination: '/teklif-al', permanent: true },
      { source: '/admin', destination: '/yonetim', permanent: false },
    ];
  },
};

export default nextConfig;
