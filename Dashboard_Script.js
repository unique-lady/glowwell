    const defaultConfig = {
      logo_glow_text: "Glow",
      logo_well_text: "Well",
      nav_dashboard: "Dashboard",
      nav_notifications: "Notifications",
      nav_workouts: "Workouts",
      nav_logout: "Log out",
      welcome_title: "Welcome, Nora",
      welcome_subtitle: "Here's an overview of your activities",
      card_progress_title: "Progress",
      card_meals_title: "Meals",
      card_timeline_title: "Timeline",
      card_tips_title: "Tips",
      background_color: "#FFF0F5",
      navbar_background: "#FFFFFF",
      glow_color: "#EC4D9C",
      well_color: "#2AC66A"
    };

    async function onConfigChange(config) {
      document.getElementById('logo-glow').textContent = config.logo_glow_text || defaultConfig.logo_glow_text;
      document.getElementById('logo-well').textContent = config.logo_well_text || defaultConfig.logo_well_text;
      // ... بقية الوظيفة (تكملة الكود الذي تم قصه في رسالتك الأصلية)
    }






