using System.Collections.ObjectModel;
using System.Text.Json;

namespace Dropblog.Services;

public class BlogConfigurationService : IBlogConfigurationService
{
    private const string StorageKey = "blog_sites";
    private int _nextId = 1;
    
    public ObservableCollection<BlogSite> BlogSites { get; private set; } = new();
    public BlogSite CurrentBlogSite { get; private set; } = BlogSite.Empty;
    
    public event EventHandler<BlogSite>? CurrentBlogSiteChanged;

    public BlogConfigurationService()
    {
        InitializeDefaultSites();
    }

    private void InitializeDefaultSites()
    {
        // Add hardcoded initial targets
        BlogSites.Add(new BlogSite(_nextId++, "NumberTools", "https://numbertools.de"));
        BlogSites.Add(new BlogSite(_nextId++, "Local Development", "http://dropblog.ddev.site/"));
        
        // Set the first site as current by default
        if (BlogSites.Count > 0)
        {
            CurrentBlogSite = BlogSites.First();
        }
    }

    public async Task LoadBlogSitesAsync()
    {
        try
        {
            var storedSites = await SecureStorage.GetAsync(StorageKey);
            if (!string.IsNullOrEmpty(storedSites))
            {
                var sites = JsonSerializer.Deserialize<List<BlogSiteData>>(storedSites);
                if (sites != null && sites.Any())
                {
                    BlogSites.Clear();
                    _nextId = 1;
                    
                    foreach (var site in sites)
                    {
                        BlogSites.Add(new BlogSite(site.Id, site.Name, site.Url));
                        if (site.Id >= _nextId)
                            _nextId = site.Id + 1;
                    }
                    
                    // Set current site
                    var currentSite = sites.FirstOrDefault(s => s.IsCurrent);
                    if (currentSite != null)
                    {
                        CurrentBlogSite = BlogSites.FirstOrDefault(s => s.Id == currentSite.Id) ?? BlogSite.Empty;
                    }
                    else if (BlogSites.Any())
                    {
                        CurrentBlogSite = BlogSites.First();
                    }
                    return;
                }
            }
            
            // If no stored sites, keep default initialization
        }
        catch (Exception ex)
        {
            // Log error and keep default initialization
            System.Diagnostics.Debug.WriteLine($"Error loading blog sites: {ex.Message}");
        }
    }

    public async Task SaveBlogSitesAsync()
    {
        try
        {
            var sitesData = BlogSites.Select(s => new BlogSiteData
            {
                Id = s.Id,
                Name = s.Name,
                Url = s.Url,
                IsCurrent = s.Id == CurrentBlogSite.Id
            }).ToList();
            
            var json = JsonSerializer.Serialize(sitesData);
            await SecureStorage.SetAsync(StorageKey, json);
        }
        catch (Exception ex)
        {
            System.Diagnostics.Debug.WriteLine($"Error saving blog sites: {ex.Message}");
        }
    }

    public async Task AddBlogSiteAsync(string name, string url)
    {
        if (string.IsNullOrWhiteSpace(name) || string.IsNullOrWhiteSpace(url))
            return;
            
        // Ensure URL has protocol
        if (!url.StartsWith("http://") && !url.StartsWith("https://"))
        {
            url = "https://" + url;
        }
        
        var newSite = new BlogSite(_nextId++, name.Trim(), url.Trim());
        BlogSites.Add(newSite);
        
        // If this is the first site, make it current
        if (CurrentBlogSite == BlogSite.Empty)
        {
            await SetCurrentBlogSiteAsync(newSite);
        }
        
        await SaveBlogSitesAsync();
    }

    public async Task RemoveBlogSiteAsync(BlogSite blogSite)
    {
        if (blogSite == null || !BlogSites.Contains(blogSite))
            return;
            
        BlogSites.Remove(blogSite);
        
        // If we removed the current site, select a new one
        if (CurrentBlogSite.Id == blogSite.Id)
        {
            var newCurrent = BlogSites.FirstOrDefault() ?? BlogSite.Empty;
            await SetCurrentBlogSiteAsync(newCurrent);
        }
        
        await SaveBlogSitesAsync();
    }

    public async Task SetCurrentBlogSiteAsync(BlogSite blogSite)
    {
        if (blogSite == null)
            blogSite = BlogSite.Empty;
            
        var oldSite = CurrentBlogSite;
        CurrentBlogSite = blogSite;
        
        if (oldSite.Id != CurrentBlogSite.Id)
        {
            CurrentBlogSiteChanged?.Invoke(this, CurrentBlogSite);
            await SaveBlogSitesAsync();
        }
    }

    private class BlogSiteData
    {
        public int Id { get; set; }
        public string Name { get; set; } = string.Empty;
        public string Url { get; set; } = string.Empty;
        public bool IsCurrent { get; set; }
    }
} 