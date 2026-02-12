/**
 * Hook Live Blue — UI/UX Standard Theme
 * استفاده به‌عنوان مرجع همه صفحات Admin Plugin
 */

export const hookLiveBlueTheme = {
  // Colors
  colors: {
    primary: {
      bg: '#0B0F19',
      secondary: '#1A1F2B',
      glass: 'rgba(26,31,43,0.22)',
      border: 'rgba(255,255,255,0.12)',
    },
    text: {
      primary: '#FFFFFF',
      secondary: '#A0AEC0',
    },
    accent: {
      purple: '#6C5DD3',
      blue: '#00E5FF',
      green: '#4ECDC4',
      red: '#FF6B6B',
      orange: '#FFB347',
    },
  },

  // Shadows
  shadows: {
    soft: '0 10px 30px rgba(0,0,0,.35)',
    glow: '0 16px 40px rgba(0,0,0,.45)',
    hover: '0 20px 50px rgba(0,0,0,.55)',
  },

  // STANDARD SPACING - همه جا یکسان
  spacing: {
    standard: '24px',  // فاصله استاندارد بین همه کارت‌ها
    small: '16px',     // فاصله کوچک داخل کارت‌ها
    large: '32px',     // فاصله بزرگ بین بخش‌ها
  },

  // Border Radius
  radii: {
    sm: '8px',
    md: '16px',
    lg: '24px',
  },

  // Transitions
  transitions: {
    fast: '0.15s ease',
    normal: '0.25s ease',
    slow: '0.4s ease',
  },

  // Container
  container: {
    maxW: '1440px',
    px: { base: '16px', md: '24px', lg: '32px' },
  },

  // Grid System - فاصله استاندارد
  grid: {
    templateColumns: { 
      base: '1fr', 
      md: 'repeat(6,1fr)', 
      lg: 'repeat(12,1fr)' 
    },
    gap: '24px',  // فاصله یکسان در همه breakpoints
    w: 'full',
    minW: 0,
  },

  // Card Base
  card: {
    bg: 'rgba(26,31,43,0.22)',
    backdropFilter: 'blur(22px)',
    border: '1px solid rgba(255,255,255,0.12)',
    borderRadius: '16px',
    boxShadow: '0 10px 30px rgba(0,0,0,.35)',
    p: { base: '16px', md: '24px' },
    transition: 'all .25s ease',
    w: 'full',
    minW: 0,
    h: 'auto',
    overflow: 'hidden',
    isolation: 'isolate',
    _hover: {
      transform: 'translateY(-2px) scale(1.02)',
      boxShadow: '0 20px 50px rgba(0,0,0,.45)',
    },
  },

  // Chart Container
  chart: {
    h: { base: '260px', md: '300px' },
    w: 'full',
    minW: 0,
  },

  // Progress Bar
  progress: {
    animation: 'progressFill 1.5s ease-in-out',
  },

  // Gauge
  gauge: {
    animation: 'gaugeGrow 1s ease-out',
  },

  // Animations
  animations: {
    fadeInUp: 'fadeInUp 0.8s ease-out',
    chartGrow: 'chartGrow 1.2s ease-out',
    progressFill: 'progressFill 1.5s ease-in-out',
    gaugeGrow: 'gaugeGrow 1s ease-out',
    gaugeBarFill: 'gaugeBarFill 2s ease-in-out',
  },

  // Keyframes
  keyframes: {
    fadeInUp: {
      from: { opacity: 0, transform: 'translateY(20px)' },
      to: { opacity: 1, transform: 'translateY(0)' },
    },
    chartGrow: {
      from: { transform: 'translateY(20px)', opacity: 0 },
      to: { transform: 'translateY(0)', opacity: 1 },
    },
    progressFill: {
      from: { width: '0%' },
      to: { width: 'var(--progress-width, 0%)' },
    },
    gaugeGrow: {
      from: { transform: 'scale(0.8)', opacity: 0 },
      to: { transform: 'scale(1)', opacity: 1 },
    },
    gaugeBarFill: {
      from: { width: '0%' },
      to: { width: '93%' },
    },
  },
};

  // CSS Variables for direct use - فاصله استاندارد
  export const hookLiveBlueCSSVars = `
  :root {
    /* Colors */
    --primary-bg: #0B0F19;
    --secondary-bg: #1A1F2B;
    --glass-bg: rgba(26, 31, 43, 0.22);
    --glass-border: rgba(255, 255, 255, 0.12);
    --text-primary: #FFFFFF;
    --text-secondary: #A0AEC0;
    
    /* Accent Colors */
    --purple: #6C5DD3;
    --blue: #00E5FF;
    --green: #4ECDC4;
    --red: #FF6B6B;
    --orange: #FFB347;
    
    /* Shadows */
    --shadow-soft: 0 10px 30px rgba(0, 0, 0, 0.35);
    --shadow-glow: 0 16px 40px rgba(0, 0, 0, 0.45);
    --shadow-hover: 0 20px 50px rgba(0, 0, 0, 0.55);
    
    /* STANDARD SPACING - همه جا یکسان */
    --gap-standard: 24px;  /* فاصله استاندارد بین همه کارت‌ها */
    --gap-small: 16px;     /* فاصله کوچک داخل کارت‌ها */
    --gap-large: 32px;     /* فاصله بزرگ بین بخش‌ها */
    
    /* Border Radius */
    --radius-sm: 8px;
    --radius-md: 16px;
    --radius-lg: 24px;
    
    /* Transitions */
    --transition-fast: 0.15s ease;
    --transition-normal: 0.25s ease;
    --transition-slow: 0.4s ease;
  }
  `;

// Usage Examples
export const usageExamples = {
  container: `
    <Container maxW="1440px" px={{base:4, md:6, lg:8}} mx="auto">
      {/* محتوا */}
    </Container>
  `,
  
  grid: `
    <Grid
      templateColumns={{ base: "1fr", md: "repeat(6,1fr)", lg: "repeat(12,1fr)" }}
      gap="24px"  /* فاصله یکسان در همه breakpoints */
      w="full"
      minW={0}
    >
      {/* GridItems */}
    </Grid>
  `,
  
  card: `
    <Box
      bg="rgba(26,31,43,0.22)"
      backdropFilter="blur(22px)"
      border="1px solid rgba(255,255,255,0.12)"
      borderRadius="16px"
      p={{base:4, md:6}}
      boxShadow="0 10px 30px rgba(0,0,0,.35)"
      _hover={{ transform:"translateY(-2px) scale(1.02)", boxShadow:"0 20px 50px rgba(0,0,0,.45)" }}
      transition="all .25s ease"
      w="full"
      minW={0}
      overflow="hidden"
      isolation="isolate"
    >
      {/* محتوای کارت */}
    </Box>
  `,
  
  chart: `
    <Box
      h={{base:'260px', md:'300px'}}
      w="full"
      minW={0}
    >
      {/* نمودار */}
    </Box>
  `,
};

export default hookLiveBlueTheme;
