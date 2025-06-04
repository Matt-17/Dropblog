using Dropblog.Services;

namespace Dropblog
{
    public partial class App : Application
    {
        public App()
        {
            InitializeComponent();
        }

        protected override Window CreateWindow(IActivationState? activationState)
        {
            return new Window(new MainPage()) { Title = "Dropblog" };
        }

        protected override async void OnStart()
        {
            base.OnStart();
            
            // Check for shared content on startup (handles both Windows and Android)
            await CheckForSharedContentOnStartup();
        }

        private async Task CheckForSharedContentOnStartup()
        {
            try
            {
                // Give the app a moment to fully initialize
                await Task.Delay(500);
                
                var preferences = Microsoft.Maui.Storage.Preferences.Default;
                
                if (preferences.Get("ShareTarget_HasContent", false))
                {
                    var shareTargetService = Handler?.MauiContext?.Services?.GetService<IShareTargetService>();
                    if (shareTargetService != null)
                    {
                        var title = preferences.Get("ShareTarget_Title", "");
                        var text = preferences.Get("ShareTarget_Text", "");
                        var uri = preferences.Get("ShareTarget_Uri", "");
                        
                        await shareTargetService.HandleSharedContentAsync(title, text, uri);
                        
                        // Clear the stored content
                        preferences.Remove("ShareTarget_HasContent");
                        preferences.Remove("ShareTarget_Title");
                        preferences.Remove("ShareTarget_Text");
                        preferences.Remove("ShareTarget_Uri");
                        preferences.Remove("ShareTarget_Timestamp");
                    }
                }
            }
            catch
            {
                // Ignore errors when checking for shared content
            }
        }
    }
}
