// Optional: App Service (Linux, PHP) in the same resource group.
// Deploy after main.bicep or merge into a single template as needed.
// az deployment group create --resource-group <rg> --template-file azure/webapp.bicep \
//   --parameters appName=<globally-unique> appServicePlanSku=B1

@description('Web app name (globally unique, e.g. globentech-app-dev).')
param appName string

@description('App Service plan SKU.')
@allowed(['B1', 'B2', 'B3', 'S1', 'S2', 'P1v2'])
param appServicePlanSku string = 'B1'

param location string = resourceGroup().location

var planName = '${appName}-plan'

resource plan 'Microsoft.Web/serverfarms@2023-12-01' = {
  name: planName
  location: location
  sku: {
    name: appServicePlanSku
  }
  properties: {
    reserved: true
  }
}

resource site 'Microsoft.Web/sites@2023-12-01' = {
  name: appName
  location: location
  properties: {
    serverFarmId: plan.id
    httpsOnly: true
    siteConfig: {
      linuxFxVersion: 'PHP|8.2'
      alwaysOn: false
      appSettings: [
        {
          name: 'SCM_DO_BUILD_DURING_DEPLOYMENT'
          value: '1'
        }
        {
          name: 'WEBSITES_ENABLE_APP_SERVICE_STORAGE'
          value: 'true'
        }
      ]
    }
  }
}

output appUrl string = 'https://${site.properties.defaultHostName}'
output appNameOut string = site.name
