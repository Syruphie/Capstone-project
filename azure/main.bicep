// Deploy: az deployment group create --resource-group <rg> --template-file azure/main.bicep \
//   --parameters serverName=<unique-name> administratorLogin=<user> administratorPassword=<pwd>
// Import schema: mysql client → source database/schema.sql against the new database (see output fqdn).

@description('Azure region for all resources.')
param location string = resourceGroup().location

@description('Globally unique MySQL flexible server name (e.g. globentech-mysql-dev).')
param serverName string

@description('MySQL administrator login.')
param administratorLogin string

@secure()
@description('MySQL administrator password.')
param administratorPassword string

@description('Application database name.')
param databaseName string = 'globentech_db'

resource server 'Microsoft.DBforMySQL/flexibleServers@2023-12-30' = {
  name: serverName
  location: location
  sku: {
    name: 'Standard_B1ms'
    tier: 'Burstable'
  }
  properties: {
    version: '8.0.21'
    administratorLogin: administratorLogin
    administratorLoginPassword: administratorPassword
    storage: {
      storageSizeGB: 32
    }
    backup: {
      backupRetentionDays: 7
      geoRedundantBackup: 'Disabled'
    }
    highAvailability: {
      mode: 'Disabled'
    }
  }
}

resource firewallAzure 'Microsoft.DBforMySQL/flexibleServers/firewallRules@2023-12-30' = {
  parent: server
  name: 'AllowAzureServices'
  properties: {
    startIpAddress: '0.0.0.0'
    endIpAddress: '0.0.0.0'
  }
}

resource db 'Microsoft.DBforMySQL/flexibleServers/databases@2023-12-30' = {
  parent: server
  name: databaseName
  properties: {
    charset: 'utf8mb4'
    collation: 'utf8mb4_unicode_ci'
  }
}

output serverId string = server.id
output fqdn string = server.properties.fullyQualifiedDomainName
output databaseNameOut string = databaseName
