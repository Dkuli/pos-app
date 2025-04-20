import { Card } from "@/components/ui/card"

export function CustomerBirthdayReminders({ customers }) {
  const upcomingBirthdays = customers.filter(c => {
    const today = new Date()
    const birthdate = new Date(c.birthdate)
    return birthdate.getMonth() === today.getMonth() && birthdate.getDate() >= today.getDate()
  })

  return (
    <Card className="p-4">
      <h3 className="font-semibold mb-2">Upcoming Birthdays</h3>
      {upcomingBirthdays.length > 0 ? (
        <ul>
          {upcomingBirthdays.map(c => (
            <li key={c.id}>{c.name} - {new Date(c.birthdate).toLocaleDateString()}</li>
          ))}
        </ul>
      ) : (
        <p>No upcoming birthdays.</p>
      )}
    </Card>
  )
}