using Microsoft.Extensions.Logging;
using Dropblog.Services;

namespace Dropblog
{
    public static class MauiProgram
    {
        public static MauiApp CreateMauiApp()
        {
            var builder = MauiApp.CreateBuilder();
            builder
                .UseMauiApp<App>()
                .ConfigureFonts(fonts =>
                {
                    fonts.AddFont("OpenSans-Regular.ttf", "OpenSansRegular");
                });

            builder.Services.AddMauiBlazorWebView();

            // Add blog configuration service
            builder.Services.AddSingleton<IBlogConfigurationService, BlogConfigurationService>();

            // Add HTTP client and API service
            builder.Services.AddHttpClient<BlogApiService>();
            
            // Add share target service
            builder.Services.AddSingleton<IShareTargetService, ShareTargetService>();

#if DEBUG
    		builder.Services.AddBlazorWebViewDeveloperTools();
    		builder.Logging.AddDebug();
#endif

            return builder.Build();
        }
    }
}
